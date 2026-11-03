<?php

namespace App\Services;

use App\Models\Dispute;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Notifications\DisputeOpened;
use App\Notifications\DisputeResolved;
use App\Support\Notifier;
use RuntimeException;

class DisputeService
{
    public function __construct(private readonly CheckoutService $checkout) {}

    /**
     * Open a dispute and freeze the order's escrow.
     *
     * @throws RuntimeException when the order can no longer be disputed
     */
    public function open(User $buyer, Order $order, string $reason, string $details, ?UploadedFile $evidence): Dispute
    {
        $evidencePath = $evidence?->store('dispute_evidence', 'local');

        try {
            return DB::transaction(function () use ($buyer, $order, $reason, $details, $evidencePath) {
                $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

                if ($order->user_id !== $buyer->id || ! $order->canBeDisputed()) {
                    throw new RuntimeException('This order can no longer be disputed.');
                }

                $dispute = new Dispute;
                $dispute->forceFill([
                    'order_id' => $order->id,
                    'opened_by' => $buyer->id,
                    'reason' => $reason,
                    'details' => $details,
                    'evidence_path' => $evidencePath,
                    'status' => Dispute::STATUS_OPEN,
                ])->save();

                $order->forceFill(['escrow_status' => Order::ESCROW_DISPUTED])->save();

                Notifier::send($order->ticket->seller, new DisputeOpened($dispute));
                Notifier::send(User::where('role', 'admin')->get(), new DisputeOpened($dispute, forAdmin: true));

                return $dispute;
            });
        } catch (\Throwable $e) {
            if ($evidencePath) {
                Storage::disk('local')->delete($evidencePath);
            }

            throw $e;
        }
    }

    /**
     * Admin decision.
     *  - refund:  the order joins the "Refunds needed" list on the Payouts page
     *  - release: escrow is released now and the seller payout is created
     */
    public function resolve(User $admin, Dispute $dispute, string $decision, string $note): void
    {
        DB::transaction(function () use ($admin, $dispute, $decision, $note) {
            $dispute = Dispute::whereKey($dispute->id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($dispute->order_id)->lockForUpdate()->firstOrFail();

            if (! $dispute->isOpen() || $order->escrow_status !== Order::ESCROW_DISPUTED) {
                throw new RuntimeException('This dispute has already been resolved.');
            }

            $dispute->forceFill([
                'status' => $decision === 'refund' ? Dispute::STATUS_REFUNDED : Dispute::STATUS_RELEASED,
                'resolution_note' => $note,
                'resolved_by' => $admin->id,
                'resolved_at' => now(),
            ])->save();

            \App\Models\AuditLog::record($decision === 'refund' ? 'dispute.refunded' : 'dispute.released', $dispute, ['order_id' => $order->id, 'note' => $note]);

            if ($decision === 'refund') {
                $order->forceFill([
                    'status' => Order::STATUS_NEEDS_REFUND,
                    'escrow_status' => Order::ESCROW_REFUNDED,
                ])->save();
            } else {
                $order->forceFill([
                    'escrow_status' => Order::ESCROW_HELD,
                    'escrow_release_at' => now(),
                ])->save();
            }
        });

        if ($decision === 'release') {
            $this->checkout->releaseDueEscrow();
        }

        $dispute->refresh()->load('order.ticket.seller', 'buyer');
        Notifier::send($dispute->buyer, new DisputeResolved($dispute));
        Notifier::send($dispute->order->ticket->seller, new DisputeResolved($dispute, forSeller: true));
    }
}
