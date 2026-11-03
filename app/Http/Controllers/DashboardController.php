<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'seller') {
            return redirect()->route('seller.dashboard');
        }

        // --- Buyer's Dashboard Logic ---
        $query = Ticket::query()->where('status', 'approved')->with('event');

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('event', function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('location', 'like', '%' . $searchTerm . '%');
            });
        }

        $tickets = $query->latest()->get(); // Use get() instead of paginate()

        return view('dashboard', [
            'tickets' => $tickets,
        ]);
    }
}
