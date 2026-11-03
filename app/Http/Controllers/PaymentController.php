<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ticket;
use App\Services\CheckoutService;
use App\Services\Mpesa\MpesaException;
use App\Services\Mpesa\PhoneNumber;
use App\Services\TicketUnavailableException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PaymentController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout) {}

    /**
     * Reserve the ticket and send the M-Pesa prompt to the buyer's phone.
     */
    public function checkout(Request $request): RedirectResponse
    {
        $request->merge(['phone_number' => PhoneNumber::clean($request->input('phone_number'))]);

        $validated = $request->validate([
            'ticket_id' => 'required|integer|exists:tickets,id',
            'phone_number' => ['required', 'string', 'regex:'.PhoneNumber::PATTERN],
        ], [
            'phone_number.regex' => 'Enter a valid Safaricom number, e.g. 0712345678.',
        ]);

        try {
            $order = $this->checkout->start($request->user(), (int) $validated['ticket_id'], $validated['phone_number']);
        } catch (TicketUnavailableException) {
            return redirect()->route('dashboard')->with('error', 'Sorry, this ticket is no longer available.');
        } catch (MpesaException) {
            return back()->withInput()->with('error', 'We could not reach M-Pesa. Please try again in a moment.');
        }

        return redirect()->route('orders.show', $order);
    }

    /**
     * "Check your phone" page, which polls status() until the payment settles.
     */
    public function show(Order $order): View|RedirectResponse
    {
        $this->authorize('view', $order);

        if ($order->isPaid()) {
            return redirect()->route('payment.success', $order->ticket_id);
        }

        return view('orders.show', [
            'order' => $order->load('ticket.event', 'payment'),
            'maskedPhone' => $order->phone_number ? PhoneNumber::mask($order->phone_number) : null,
        ]);
    }

    /**
     * Polled by the waiting page. Falls back to asking Safaricom directly
     * (at most every 5 seconds per order) in case the callback is slow.
     */
    public function status(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        if ($order->isPending() && Cache::add("mpesa-query-order-{$order->id}", true, 5)) {
            $order = $this->checkout->refresh($order);
        }

        return response()->json([
            'status' => $order->status,
            'message' => $this->statusMessage($order),
            'redirect' => $order->isPaid() ? route('payment.success', $order->ticket_id) : null,
        ]);
    }

    /**
     * The ticket and its QR code, shown once payment is confirmed.
     */
    public function paymentSuccess(Ticket $ticket): View
    {
        $this->authorize('viewPurchased', $ticket);

        $qrCode = QrCode::size(250)->generate($ticket->validation_code);

        return view('payment.success', [
            'ticket' => $ticket,
            'qrCode' => $qrCode,
        ]);
    }

    private function statusMessage(Order $order): string
    {
        return match ($order->status) {
            Order::STATUS_PENDING => 'Waiting for you to approve the payment on your phone…',
            Order::STATUS_PAID => 'Payment received! Loading your ticket…',
            Order::STATUS_EXPIRED => 'The payment window ran out, so the ticket was released.',
            Order::STATUS_NEEDS_REFUND => 'We received your payment but the ticket was no longer available. Our team will refund you.',
            default => 'The payment was not completed'.($order->payment?->result_desc ? ': '.$order->payment->result_desc : '.'),
        };
    }
}
