<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payout;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SellerDashboardController extends Controller
{
    /**
     * Tabs on the listings table: key => [label, statuses].
     */
    private const TABS = [
        'all' => ['All', null],
        'live' => ['Live', [Ticket::STATUS_APPROVED, Ticket::STATUS_RESERVED]],
        'review' => ['In review', [Ticket::STATUS_PENDING]],
        'sold' => ['Sold', [Ticket::STATUS_SOLD]],
        'rejected' => ['Rejected', [Ticket::STATUS_REJECTED]],
    ];

    public function index(Request $request): View
    {
        /** @var \App\Models\User $seller */
        $seller = Auth::user();

        // Money from sales, after TixSwap's commission.
        $soldOrders = Order::query()
            ->where('status', Order::STATUS_PAID)
            ->whereHas('ticket', fn ($q) => $q->where('user_id', $seller->id));

        $earnings = [
            'in_escrow' => (clone $soldOrders)->whereIn('escrow_status', [Order::ESCROW_HELD, Order::ESCROW_DISPUTED])->sum('seller_amount'),
            'disputed' => (clone $soldOrders)->where('escrow_status', Order::ESCROW_DISPUTED)->sum('seller_amount'),
            'awaiting_payout' => $seller->payouts()->where('status', Payout::STATUS_PENDING)->sum('amount'),
            'paid_out' => $seller->payouts()->where('status', Payout::STATUS_PAID)->sum('amount'),
        ];

        $tab = array_key_exists($request->query('tab'), self::TABS) ? $request->query('tab') : 'all';

        // Count per tab for the tab labels.
        $statusCounts = $seller->tickets()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $tabs = collect(self::TABS)->map(fn ($def) => [
            'label' => $def[0],
            'count' => $def[1] === null ? $statusCounts->sum() : collect($def[1])->sum(fn ($s) => $statusCounts[$s] ?? 0),
        ]);

        $tickets = $seller->tickets()
            ->with('event')
            ->when(self::TABS[$tab][1], fn ($q, $statuses) => $q->whereIn('status', $statuses))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('seller.dashboard', [
            'tickets' => $tickets,
            'earnings' => $earnings,
            'tabs' => $tabs,
            'tab' => $tab,
            'hasListings' => $statusCounts->sum() > 0,
            'needsPayoutPhone' => ! $seller->payout_phone,
            'disputedTicketIds' => (clone $soldOrders)->where('escrow_status', Order::ESCROW_DISPUTED)->pluck('ticket_id')->all(),
        ]);
    }
}
