<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TicketController;
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
        abort_if(in_array($ticket->status, [Ticket::STATUS_SOLD, Ticket::STATUS_RESERVED]), 403, 'Sold or reserved tickets cannot be edited.');

        return view('admin.listings.edit', ['ticket' => $ticket]);
    }

    public function update(Request $request, Ticket $ticket)
    {
        abort_if(in_array($ticket->status, [Ticket::STATUS_SOLD, Ticket::STATUS_RESERVED]), 403, 'Sold or reserved tickets cannot be edited.');

        $validated = $request->validate([
            'price' => 'required|numeric|min:1|max:1000000',
            'seat_details' => 'required|string|max:255',
        ]);

        $before = $ticket->only(['price', 'seat_details']);
        $ticket->update($validated);
        \App\Models\AuditLog::record('listing.updated', $ticket, ['before' => $before, 'after' => $ticket->only(['price', 'seat_details'])]);
        return redirect()->route('admin.listings.index')->with('success', 'Listing updated.');
    }

    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);

        \App\Models\AuditLog::record('listing.deleted', $ticket, ['event' => $ticket->event?->name, 'seller' => $ticket->seller?->email, 'price' => $ticket->price]);
        TicketController::deleteWithFiles($ticket);
        return back()->with('success', 'Ticket listing has been deleted.');
    }
}