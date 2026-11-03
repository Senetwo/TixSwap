<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Mpesa\MpesaException;
use App\Services\Mpesa\MpesaGateway;
use App\Services\Mpesa\PhoneNumber;
use App\Services\Mpesa\StkResult;
use App\Notifications\PaymentNeedsRefund;
use App\Notifications\PurchaseConfirmed;
use App\Notifications\TicketSold;
use App\Support\Notifier;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * The whole money flow for buying a ticket:
 *
 *   1. start()    reserve the ticket, create a pending order, send the STK push
 *   2. complete() apply Safaricom's result (from the callback or a status query)
 *                 paid   -> ticket sold to the buyer, money held in escrow
 *                 failed -> reservation released, ticket back on sale
 *   3. expireStale()       give up on orders nobody paid for in time
 *   4. releaseDueEscrow()  after the event, release escrow and create the seller payout
 *
 * Every step runs in a transaction with row locks, and every step is safe
 * to run twice (Safaricom can send the same callback more than once).
 */
class CheckoutService
{
    public function __construct(private readonly MpesaGateway $mpesa) {}

    /**
     * @throws TicketUnavailableException when the ticket can't be bought by this user
     * @throws MpesaException when the STK push could not be sent
     */
    public function start(User $buyer, int $ticketId, string $phone): Order
    {
        $msisdn = PhoneNumber::toMsisdn($phone)
            ?? throw new \InvalidArgumentException('Invalid phone number.');

        $order = DB::transaction(function () use ($buyer, $ticketId, $msisdn) {
            $ticket = Ticket::whereKey($ticketId)->lockForUpdate()->firstOrFail();

            // Double-click / page refresh: reuse the buyer's own live order.
            if ($ticket->isReserved()) {
                $existing = $ticket->orders()
                    ->where('user_id', $buyer->id)
                    ->where('status', Order::STATUS_PENDING)
                    ->latest('id')
                    ->first();

                if ($existing) {
                    return $existing;
                }
            }

            if ($buyer->cannot('purchase', $ticket)) {
                throw new TicketUnavailableException('This ticket is no longer available.');
            }

            // A previous buyer's reservation ran out: close their order first.
            $this->expireOrdersFor($ticket);

            $minutes = (int) config('services.tixswap.reservation_minutes', 10);
            $amount = (int) ceil((float) $ticket->price); // M-Pesa only takes whole shillings
            $commission = round($amount * ((float) config('services.tixswap.commission_percent', 10)) / 100, 2);

            $ticket->forceFill([
                'status' => Ticket::STATUS_RESERVED,
                'reserved_until' => now()->addMinutes($minutes),
            ])->save();

            $order = new Order(['user_id' => $buyer->id, 'ticket_id' => $ticket->id]);
            $order->forceFill([
                'amount' => $amount,
                'commission' => $commission,
                'seller_amount' => $amount - $commission,
                'phone_number' => $msisdn,
                'status' => Order::STATUS_PENDING,
                'expires_at' => $ticket->reserved_until,
            ])->save();

            return $order;
        });

        // Already has a push in flight (the reuse case above).
        if ($order->payments()->exists()) {
            return $order;
        }

        try {
            $push = $this->mpesa->stkPush(
                msisdn: $msisdn,
                amount: (int) $order->amount,
                accountReference: 'TIX'.$order->id,
                description: 'TixSwap ticket',
                callbackUrl: $this->callbackUrl(),
            );
        } catch (MpesaException $e) {
            Log::warning('STK push failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            $this->failOrder($order, null, 'Could not send the M-Pesa prompt.');

            throw $e;
        }

        $payment = new Payment;
        $payment->forceFill([
            'order_id' => $order->id,
            'amount' => $order->amount,
            'payment_method' => 'mpesa',
            'status' => Payment::STATUS_PENDING,
            'checkout_request_id' => $push->checkoutRequestId,
            'merchant_request_id' => $push->merchantRequestId,
            'phone_number' => $msisdn,
        ])->save();

        return $order;
    }

    /**
     * Apply Safaricom's verdict on an STK push. Returns the order, or null
     * if the CheckoutRequestID is unknown.
     */
    public function complete(StkResult $result): ?Order
    {
        return DB::transaction(function () use ($result) {
            $payment = Payment::where('checkout_request_id', $result->checkoutRequestId)->lockForUpdate()->first();

            if (! $payment) {
                Log::warning('M-Pesa result for unknown CheckoutRequestID', ['id' => $result->checkoutRequestId]);

                return null;
            }

            $order = Order::whereKey($payment->order_id)->lockForUpdate()->firstOrFail();

            // Already recorded as paid. A later callback may still carry the
            // receipt number that a status query didn't have.
            if ($payment->status === Payment::STATUS_PAID) {
                if (! $payment->transaction_id && $result->receiptNumber) {
                    $payment->forceFill(['transaction_id' => $result->receiptNumber, 'raw_callback' => $result->raw ?: $payment->raw_callback])->save();
                }

                return $order;
            }

            // Already failed and this is another failure: nothing to do.
            // (A *successful* result is always applied, even if we had given
            // up on the order: the buyer's money has moved.)
            if ($payment->status === Payment::STATUS_FAILED && ! $result->successful()) {
                return $order;
            }

            $payment->forceFill([
                'result_code' => $result->resultCode,
                'result_desc' => Str::limit($result->resultDesc, 250),
                'raw_callback' => $result->raw ?: null,
            ]);

            if (! $result->successful()) {
                $payment->forceFill(['status' => Payment::STATUS_FAILED])->save();
                $this->failOrder($order, Order::STATUS_FAILED, $result->resultDesc);

                return $order->refresh();
            }

            $payment->forceFill([
                'status' => Payment::STATUS_PAID,
                'transaction_id' => $result->receiptNumber,
                'paid_at' => now(),
            ])->save();

            if ($order->isPaid()) {
                return $order;
            }

            $ticket = Ticket::whereKey($order->ticket_id)->lockForUpdate()->first();
            $amountOk = $result->amount === null || $result->amount >= (int) $order->amount;
            $deliverable = $ticket
                && $ticket->buyer_id === null
                && ($ticket->status === Ticket::STATUS_APPROVED || $ticket->status === Ticket::STATUS_RESERVED)
                && ! $this->reservedForSomeoneElse($ticket, $order);

            if (! $amountOk || ! $deliverable) {
                // Money arrived but we can't hand over the ticket (paid late
                // after someone else bought it, or underpaid). Flag for refund.
                Log::error('Paid order cannot be fulfilled; refund required', [
                    'order_id' => $order->id, 'amount_ok' => $amountOk, 'deliverable' => $deliverable,
                ]);
                $order->forceFill(['status' => Order::STATUS_NEEDS_REFUND, 'paid_at' => now()])->save();
                Notifier::send($order->buyer, new PaymentNeedsRefund($order));

                return $order;
            }

            $ticket->forceFill([
                'status' => Ticket::STATUS_SOLD,
                'buyer_id' => $order->user_id,
                'validation_code' => (string) Str::uuid(),
                'reserved_until' => null,
            ])->save();

            $order->forceFill([
                'status' => Order::STATUS_PAID,
                'paid_at' => now(),
                'escrow_status' => Order::ESCROW_HELD,
                'escrow_release_at' => $this->escrowReleaseTime($ticket),
            ])->save();

            Notifier::send($order->buyer, new PurchaseConfirmed($order));
            Notifier::send($ticket->seller, new TicketSold($order));

            return $order;
        });
    }

    /**
     * For a pending order, ask Safaricom directly whether it was paid.
     * Covers callbacks that are slow or never arrive.
     */
    public function refresh(Order $order): Order
    {
        if (! $order->isPending()) {
            return $order;
        }

        $payment = $order->payment()->first();

        if ($payment && $payment->status === Payment::STATUS_PENDING) {
            try {
                $result = $this->mpesa->query($payment->checkout_request_id);
            } catch (MpesaException $e) {
                Log::warning('STK query failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                $result = null;
            }

            if ($result) {
                return $this->complete($result) ?? $order->refresh();
            }
        }

        // Nobody paid and the reservation is over (with a short grace
        // period for a callback that's on its way).
        if ($order->expires_at && $order->expires_at->lt(now()->subMinutes(2))) {
            $this->failOrder($order, Order::STATUS_EXPIRED, 'Reservation expired before payment.');
        }

        return $order->refresh();
    }

    /**
     * Scheduled: settle every pending order whose reservation has run out.
     */
    public function expireStale(): int
    {
        $count = 0;

        Order::where('status', Order::STATUS_PENDING)
            ->where('expires_at', '<', now()->subMinutes(2))
            ->each(function (Order $order) use (&$count) {
                if (! $this->refresh($order)->isPending()) {
                    $count++;
                }
            });

        return $count;
    }

    /**
     * Scheduled: once the escrow period is over, release the money and
     * record what we owe the seller.
     */
    public function releaseDueEscrow(): int
    {
        $count = 0;

        Order::where('status', Order::STATUS_PAID)
            ->where('escrow_status', Order::ESCROW_HELD)
            ->where('escrow_release_at', '<=', now())
            ->pluck('id')
            ->each(function (int $orderId) use (&$count) {
                DB::transaction(function () use ($orderId, &$count) {
                    $order = Order::whereKey($orderId)->lockForUpdate()->first();

                    if (! $order || $order->escrow_status !== Order::ESCROW_HELD) {
                        return;
                    }

                    $sellerId = Ticket::whereKey($order->ticket_id)->value('user_id');

                    $payout = new Payout;
                    $payout->forceFill([
                        'seller_id' => $sellerId,
                        'order_id' => $order->id,
                        'amount' => $order->seller_amount,
                        'status' => Payout::STATUS_PENDING,
                    ])->save();

                    $order->forceFill(['escrow_status' => Order::ESCROW_RELEASED])->save();
                    $count++;
                });
            });

        return $count;
    }

    public function callbackUrl(): string
    {
        $secret = config('services.mpesa.callback_secret');

        if (! $secret) {
            if (config('services.mpesa.driver') === 'daraja') {
                throw new MpesaException('Set MPESA_CALLBACK_SECRET in .env before taking payments.');
            }
            $secret = 'not-configured';
        }

        $base = config('services.mpesa.callback_base_url') ?: config('app.url');

        return rtrim($base, '/').'/mpesa/callback/'.$secret;
    }

    /**
     * Mark an order as failed/expired, its pending payment as failed, and
     * put the ticket back on sale if this order was holding it.
     */
    private function failOrder(Order $order, ?string $status, string $reason): void
    {
        DB::transaction(function () use ($order, $status, $reason) {
            $order = Order::whereKey($order->id)->lockForUpdate()->first();

            if (! $order || ! $order->isPending()) {
                return;
            }

            $order->forceFill(['status' => $status ?? Order::STATUS_FAILED])->save();

            $order->payments()->where('status', Payment::STATUS_PENDING)->each(function (Payment $p) use ($reason) {
                $p->forceFill(['status' => Payment::STATUS_FAILED, 'result_desc' => $p->result_desc ?? Str::limit($reason, 250)])->save();
            });

            $ticket = Ticket::whereKey($order->ticket_id)->lockForUpdate()->first();

            if ($ticket && $ticket->status === Ticket::STATUS_RESERVED && ! $this->reservedForSomeoneElse($ticket, $order)) {
                $ticket->forceFill(['status' => Ticket::STATUS_APPROVED, 'reserved_until' => null])->save();
            }
        });
    }

    /**
     * Expire pending orders on a ticket whose reservation has run out.
     */
    private function expireOrdersFor(Ticket $ticket): void
    {
        $ticket->orders()->where('status', Order::STATUS_PENDING)->each(function (Order $order) {
            $order->forceFill(['status' => Order::STATUS_EXPIRED])->save();
            $order->payments()->where('status', Payment::STATUS_PENDING)
                ->update(['status' => Payment::STATUS_FAILED, 'result_desc' => 'Reservation expired before payment.']);
        });
    }

    private function reservedForSomeoneElse(Ticket $ticket, Order $order): bool
    {
        return $ticket->orders()
            ->whereKeyNot($order->id)
            ->where('status', Order::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Hold the buyer's money until the event is over (plus a buffer for
     * "my ticket didn't work" complaints).
     */
    private function escrowReleaseTime(Ticket $ticket): Carbon
    {
        $hours = (int) config('services.tixswap.escrow_release_hours_after_event', 24);
        $eventDate = $ticket->event?->date ? Carbon::parse($ticket->event->date) : now();

        return ($eventDate->isPast() ? now() : $eventDate)->copy()->addHours($hours);
    }
}
