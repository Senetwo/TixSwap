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
        $pendingTickets = Ticket::where('status', Ticket::STATUS_PENDING)
                                ->with(['seller', 'event'])
                                ->latest()
                                ->get();

        return view('admin.tickets.pending', [
            'tickets' => $pendingTickets,
            // Other listings with the same proof file or ticket number.
            'duplicates' => $pendingTickets->mapWithKeys(fn (Ticket $t) => [$t->id => $t->possibleDuplicates()]),
        ]);
    }

    /**
     * Approve the specified ticket.
     */
    public function approve(Ticket $ticket): RedirectResponse
    {
        abort_unless($ticket->status === Ticket::STATUS_PENDING, 422, 'Only pending tickets can be approved.');

        $ticket->forceFill(['status' => Ticket::STATUS_APPROVED, 'admin_notes' => null])->save();
        \App\Models\AuditLog::record('listing.approved', $ticket);
        \App\Support\Notifier::send($ticket->seller, new \App\Notifications\ListingApproved($ticket));
        // You could add a notification to the seller here
        return redirect()->route('admin.tickets.pending')->with('success', 'Ticket has been approved.');
    }

    /**
     * Reject the specified ticket and record a reason.
     */
    public function reject(Request $request, Ticket $ticket): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        abort_unless($ticket->status === Ticket::STATUS_PENDING, 422, 'Only pending tickets can be rejected.');

        $ticket->forceFill([
            'status' => Ticket::STATUS_REJECTED,
            'admin_notes' => $request->rejection_reason,
        ])->save();
        \App\Models\AuditLog::record('listing.rejected', $ticket, ['reason' => $request->rejection_reason]);
        \App\Support\Notifier::send($ticket->seller, new \App\Notifications\ListingRejected($ticket));

        return redirect()->route('admin.tickets.pending')->with('success', 'Ticket has been rejected.');
    }
}