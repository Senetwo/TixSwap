<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $paid = Order::whereNotNull('paid_at')->whereNotIn('status', [Order::STATUS_REFUNDED, Order::STATUS_NEEDS_REFUND]);

        return view('admin.dashboard', [
            'kpis' => [
                'sales' => (clone $paid)->sum('amount'),
                'revenue' => (clone $paid)->sum('commission'),
                'escrow' => Order::where('status', Order::STATUS_PAID)
                    ->whereIn('escrow_status', [Order::ESCROW_HELD, Order::ESCROW_DISPUTED])->sum('amount'),
                'sold' => Ticket::where('status', Ticket::STATUS_SOLD)->count(),
                'live' => Ticket::whereIn('status', [Ticket::STATUS_APPROVED, Ticket::STATUS_RESERVED])->count(),
                'users' => User::count(),
            ],
            'attention' => [
                ['Listings to review', Ticket::where('status', Ticket::STATUS_PENDING)->count(), 'admin.tickets.pending', 'clipboard-check'],
                ['Open disputes', Dispute::where('status', Dispute::STATUS_OPEN)->count(), 'admin.disputes.index', 'scale'],
                ['Refunds to send', Order::where('status', Order::STATUS_NEEDS_REFUND)->count(), 'admin.payouts.index', 'banknotes'],
                ['Payouts to send', Payout::where('status', Payout::STATUS_PENDING)->count(), 'admin.payouts.index', 'banknotes'],
            ],
            'recent' => Ticket::with(['event', 'seller'])->latest()->take(6)->get(),
        ]);
    }
}
