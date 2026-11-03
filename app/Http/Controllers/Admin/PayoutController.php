<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Money going out: seller payouts and buyer refunds.
 *
 * For now an admin sends the money from the M-Pesa business account and
 * records the receipt here. Automatic M-Pesa B2C payouts can replace the
 * "mark as paid" step later.
 */
class PayoutController extends Controller
{
    public function index(): View
    {
        return view('admin.payouts.index', [
            'pending' => Payout::with(['seller', 'order.ticket.event'])
                ->whereIn('status', [Payout::STATUS_PENDING, Payout::STATUS_FAILED, Payout::STATUS_PROCESSING])
                ->oldest()
                ->get(),
            'recent' => Payout::with(['seller', 'order.ticket.event'])
                ->where('status', Payout::STATUS_PAID)
                ->latest('paid_at')
                ->limit(20)
                ->get(),
            'refunds' => Order::with(['buyer', 'ticket.event', 'payment'])
                ->where('status', Order::STATUS_NEEDS_REFUND)
                ->oldest('paid_at')
                ->get(),
            'heldInEscrow' => Order::where('status', Order::STATUS_PAID)
                ->where('escrow_status', Order::ESCROW_HELD)
                ->sum('amount'),
            'b2cEnabled' => \App\Services\PayoutService::enabled(),
        ]);
    }

    public function markPaid(Request $request, Payout $payout): RedirectResponse
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:50',
        ]);

        $updated = DB::transaction(function () use ($payout, $validated) {
            $payout = Payout::whereKey($payout->id)->lockForUpdate()->first();

            // Manual payment is allowed unless it's already paid. (For a
            // "processing" payout the admin is confirming M-Pesa paid it.)
            if ($payout->status === Payout::STATUS_PAID) {
                return false;
            }

            $payout->forceFill([
                'status' => Payout::STATUS_PAID,
                'method' => $payout->method === 'mpesa_b2c' ? 'mpesa_b2c' : 'manual',
                'reference' => strtoupper(trim($validated['reference'])),
                'paid_at' => now(),
            ])->save();

            return true;
        });

        if ($updated) {
            \App\Models\AuditLog::record('payout.paid', $payout, ['amount' => $payout->amount, 'reference' => strtoupper(trim($validated['reference']))]);
            \App\Support\Notifier::send($payout->seller, new \App\Notifications\PayoutSent($payout->fresh(['order.ticket.event'])));
        }

        return back()->with($updated ? 'success' : 'error', $updated ? 'Payout marked as paid.' : 'That payout was already processed.');
    }

    public function send(Payout $payout, \App\Services\PayoutService $payouts): RedirectResponse
    {
        try {
            $payout = $payouts->send($payout);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $payout->status === Payout::STATUS_PAID
            ? 'Payout sent. M-Pesa receipt '.$payout->reference.'.'
            : 'Payout sent to M-Pesa. It will be marked paid when M-Pesa confirms.');
    }

    public function sendAll(\App\Services\PayoutService $payouts): RedirectResponse
    {
        if (! \App\Services\PayoutService::enabled()) {
            return back()->with('error', 'M-Pesa payouts are switched off.');
        }

        [$sent, $failed] = $payouts->sendAllPending();

        return back()->with($failed ? 'error' : 'success', "Sent {$sent} ".Str::plural('payout', $sent).'.'.($failed ? " {$failed} failed; see the reasons below." : ''));
    }

    public function confirmFailed(Payout $payout, \App\Services\PayoutService $payouts): RedirectResponse
    {
        try {
            $payouts->confirmFailed($payout);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Marked as failed. You can send it again.');
    }

    public function markRefunded(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'reference' => 'required|string|max:50',
        ]);

        $updated = DB::transaction(function () use ($order) {
            $order = Order::whereKey($order->id)->lockForUpdate()->first();

            if ($order->status !== Order::STATUS_NEEDS_REFUND) {
                return false;
            }

            $order->forceFill([
                'status' => Order::STATUS_REFUNDED,
                'escrow_status' => Order::ESCROW_REFUNDED,
            ])->save();

            return true;
        });

        // Keep the refund receipt with the payment record for audits.
        if ($updated && $payment = $order->payment()->first()) {
            $payment->forceFill([
                'result_desc' => \Illuminate\Support\Str::limit('Refunded: '.strtoupper(trim($request->input('reference'))).' | '.$payment->result_desc, 250),
            ])->save();
        }

        if ($updated) {
            \App\Models\AuditLog::record('order.refunded', $order, ['amount' => $order->amount, 'reference' => strtoupper(trim($request->input('reference')))]);
            \App\Support\Notifier::send($order->buyer, new \App\Notifications\RefundSent($order->fresh(['ticket.event']), strtoupper(trim($request->input('reference')))));
        }

        return back()->with($updated ? 'success' : 'error', $updated ? 'Order marked as refunded.' : 'That order was already processed.');
    }
}
