<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                ->where('status', Payout::STATUS_PENDING)
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
        ]);
    }

    public function markPaid(Request $request, Payout $payout): RedirectResponse
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:50',
        ]);

        $updated = DB::transaction(function () use ($payout, $validated) {
            $payout = Payout::whereKey($payout->id)->lockForUpdate()->first();

            if ($payout->status !== Payout::STATUS_PENDING) {
                return false;
            }

            $payout->forceFill([
                'status' => Payout::STATUS_PAID,
                'reference' => strtoupper(trim($validated['reference'])),
                'paid_at' => now(),
            ])->save();

            return true;
        });

        return back()->with($updated ? 'success' : 'error', $updated ? 'Payout marked as paid.' : 'That payout was already processed.');
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

        return back()->with($updated ? 'success' : 'error', $updated ? 'Order marked as refunded.' : 'That order was already processed.');
    }
}
