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
            'total_earnings' => (clone $sellerTicketsQuery)->where('status', 'sold')->sum('price'),
        ];

        // Fetch recent tickets listed by this seller for display.
        // We use 'with('event')' to eager-load the event details to prevent N+1 issues.
        $tickets = $seller->tickets()
                                ->with('event')
                                ->latest()
                                ->paginate(10); // Using pagination is better for long lists

        return view('seller.dashboard', [
            'stats' => $stats,
            'tickets' => $tickets
        ]);
    }
}
