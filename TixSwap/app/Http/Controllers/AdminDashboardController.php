<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'pendingTickets' => Ticket::where('status', 'pending')->count(),
            'activeListings' => Ticket::where('status', 'approved')->count(),
            'ticketsSold' => Ticket::where('status', 'sold')->count(),
            'totalSalesValue' => Ticket::where('status', 'sold')->sum('price'),
        ];

        $allTickets = Ticket::with('event', 'seller')->latest()->take(5)->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'allTickets' => $allTickets
        ]);
    }
}
