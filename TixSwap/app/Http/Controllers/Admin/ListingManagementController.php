<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListingManagementController extends Controller
{
    /**
     * Display a listing of all tickets on the platform.
     */
    public function index(): View
    {
        $allTickets = Ticket::with(['seller', 'event'])
                              ->latest()
                              ->get();

        return view('admin.listings.index', ['tickets' => $allTickets]);
    }

    public function edit(Ticket $ticket)
    {
        return view('admin.listings.edit', ['ticket' => $ticket]);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'seat_details' => 'required|string|max:255',
        ]);

        $ticket->update($validated);
        return redirect()->route('admin.dashboard')->with('success', 'Ticket updated successfully.');
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return back()->with('success', 'Ticket listing has been deleted.');
    }
}