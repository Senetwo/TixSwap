<?php
// In app/Http/Controllers/Admin/TicketVerificationController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TicketVerificationController extends Controller
{
    /**
     * Display a listing of the tickets pending approval.
     */
    public function index(): View
    {
        $pendingTickets = Ticket::where('status', 'pending')
                                ->with(['seller', 'event'])
                                ->latest()
                                ->get();

        return view('admin.tickets.pending', ['tickets' => $pendingTickets]);
    }

    /**
     * Approve the specified ticket.
     */
    public function approve(Ticket $ticket): RedirectResponse
    {
        $ticket->update(['status' => 'approved']);
        // You could add a notification to the seller here
        return redirect()->route('admin.tickets.pending')->with('success', 'Ticket has been approved.');
    }

    /**
     * Reject the specified ticket.
     */
    /**
     * Reject the specified ticket and record a reason.
     */
    public function reject(Request $request, Ticket $ticket): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $ticket->update([
            'status' => 'rejected',
            'admin_notes' => $request->rejection_reason,
        ]);

        return redirect()->route('admin.tickets.pending')->with('success', 'Ticket has been rejected.');
    }
}