<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode; // Make sure to import this

class TicketController extends Controller
{
    public function create(): View
    {
        return view('seller.tickets.create');
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $eventImagePath = $request->file('event_image')->store('event_images', 'public');
            $purchaseProofPath = $request->file('purchase_proof')->store('purchase_proofs', 'public');

            $event = Event::create([
                'name' => $validated['event_name'],
                'location' => $validated['location'],
                'date' => $validated['date'],
                'image_url' => $eventImagePath,
                'original_vendor' => $validated['original_vendor'] ?? 'N/A',
            ]);

            // This now includes every required field from your database table
            Ticket::create([
                'event_id' => $event->id,
                'user_id' => Auth::id(), // Matches your 'user_id' column
                'price' => $validated['price'],
                'seat_details' => $validated['seat_details'],
                'original_vendor' => $validated['original_vendor'] ?? 'N/A',
                'status' => 'pending',
                'purchase_proof' => $purchaseProofPath,
            ]);

            DB::commit();

            return redirect()->route('seller.dashboard')->with('success', 'Your ticket has been listed successfully and is pending review.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ticket creation failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            if (isset($eventImagePath)) { Storage::disk('public')->delete($eventImagePath); }
            if (isset($purchaseProofPath)) { Storage::disk('public')->delete($purchaseProofPath); }

            return back()->with('error', 'There was a problem creating your ticket. Please try again.')->withInput();
        }
    }



    /**
     * Display a specific ticket for purchasing.
     * This is the page the user sees after clicking "Buy Now".
     */
    public function show(Ticket $ticket): View
    {
        // Ensure only approved tickets can be viewed publicly
        abort_if($ticket->status !== 'approved', 404);

        return view('tickets.show', ['ticket' => $ticket]);
    }

    /**
     * Display a specific ticket that the user has purchased.
     */
    public function showPurchasedTicket(Ticket $ticket): View
    {
        // Security check: Ensure the logged-in user is the buyer of this ticket.
        abort_if($ticket->buyer_id !== Auth::id(), 403, 'This is not your ticket.');

        // Generate the QR code for display on the ticket page
        // The QR code should contain the unique validation code for scanning
        $qrCode = QrCode::size(250)->generate($ticket->validation_code);

        return view('tickets.purchased_show', [
            'ticket' => $ticket,
            'qrCode' => $qrCode,
        ]);
    }
}
