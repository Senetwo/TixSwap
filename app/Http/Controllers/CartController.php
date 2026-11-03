<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket; // Make sure to import the Ticket model

class CartController extends Controller
{
    /**
     * Display a listing of the tickets purchased by the user.
     * This corresponds to the "My Tickets" page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get the currently authenticated user's ID
        $userId = Auth::id();

        // Fetch all tickets where the 'buyer_id' matches the current user's ID.
        // We also eager-load the 'event' relationship to avoid N+1 query problems.
        $purchasedTickets = Ticket::where('buyer_id', $userId)
                                  ->with('event')
                                  ->latest() // Order by the most recently purchased
                                  ->get();

        // Pass the fetched tickets to the view under the variable name 'tickets'.
        // The view expects a variable named '$tickets'.
        return view('cart.index', ['tickets' => $purchasedTickets]);
    }
}
