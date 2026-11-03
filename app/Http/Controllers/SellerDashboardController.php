<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SellerDashboardController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $seller */
        $seller = Auth::user();
        $sellerTicketsQuery = $seller->tickets();

        // Calculate statistics for the seller's dashboard
        $stats = [
            'pending' => (clone $sellerTicketsQuery)->where('status', 'pending')->count(),
            'approved' => (clone $sellerTicketsQuery)->where('status', 'approved')->count(),
            'sold' => (clone $sellerTicketsQuery)->where('status', 'sold')->count(),
        ];

        // Money from sales, after TixSwap's commission.
        $soldOrders = \App\Models\Order::query()
            ->where('status', \App\Models\Order::STATUS_PAID)
            ->whereHas('ticket', fn ($q) => $q->where('user_id', $seller->id));

        $earnings = [
            'in_escrow' => (clone $soldOrders)->where('escrow_status', \App\Models\Order::ESCROW_HELD)->sum('seller_amount'),
            'awaiting_payout' => $seller->payouts()->where('status', \App\Models\Payout::STATUS_PENDING)->sum('amount'),
            'paid_out' => $seller->payouts()->where('status', \App\Models\Payout::STATUS_PAID)->sum('amount'),
        ];

        // Fetch recent tickets listed by this seller for display.
        // We use 'with('event')' to eager-load the event details to prevent N+1 issues.
        $tickets = $seller->tickets()
                                ->with('event')
                                ->latest()
                                ->paginate(10); // Using pagination is better for long lists

        return view('seller.dashboard', [
            'stats' => $stats,
            'earnings' => $earnings,
            'needsPayoutPhone' => ! $seller->payout_phone,
            'tickets' => $tickets
        ]);
    }
}
