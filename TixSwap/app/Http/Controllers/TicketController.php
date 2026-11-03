<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketController extends Controller
{
    /**
     * Proofs of purchase contain personal data, so they live on the private
     * 'local' disk (storage/app/private) and are only served through
     * showProof(), which checks permissions. Event images stay public.
     */
    private const PROOF_DISK = 'local';

    public function create(): View
    {
        return view('seller.tickets.create');
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $eventImagePath = $request->file('event_image')->store('event_images', 'public');
            $purchaseProofPath = $request->file('purchase_proof')->store('purchase_proofs', self::PROOF_DISK);

            DB::transaction(function () use ($validated, $eventImagePath, $purchaseProofPath) {
                $event = Event::create([
                    'name' => $validated['event_name'],
                    'location' => $validated['location'],
                    'date' => $validated['date'],
                    'image_url' => $eventImagePath,
                    'original_vendor' => $validated['original_vendor'] ?? 'N/A',
                ]);

                $ticket = new Ticket([
                    'event_id' => $event->id,
                    'user_id' => Auth::id(),
                    'price' => $validated['price'],
                    'seat_details' => $validated['seat_details'],
                    'original_vendor' => $validated['original_vendor'] ?? 'N/A',
                    'purchase_proof' => $purchaseProofPath,
                ]);
                $ticket->forceFill(['status' => Ticket::STATUS_PENDING])->save();
            });

            return redirect()->route('seller.dashboard')->with('success', 'Your ticket has been listed successfully and is pending review.');
        } catch (\Throwable $e) {
            Log::error('Ticket creation failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            if (isset($eventImagePath)) {
                Storage::disk('public')->delete($eventImagePath);
            }
            if (isset($purchaseProofPath)) {
                Storage::disk(self::PROOF_DISK)->delete($purchaseProofPath);
            }

            return back()->with('error', 'There was a problem creating your ticket. Please try again.')->withInput();
        }
    }

    public function edit(Ticket $ticket): View
    {
        $this->authorize('update', $ticket);

        return view('seller.tickets.edit', ['ticket' => $ticket->load('event')]);
    }

    /**
     * Update a listing. Any change to an approved listing sends it back to
     * admin review, so a seller cannot alter a ticket after it was checked.
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $ticket->fill($request->validated());

        if ($ticket->isDirty()) {
            $ticket->forceFill(['status' => Ticket::STATUS_PENDING, 'admin_notes' => null]);
        }

        $ticket->save();

        return redirect()->route('seller.dashboard')->with('success', 'Listing updated. It will be reviewed again before going live.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $this->authorize('delete', $ticket);

        $this->deleteWithFiles($ticket);

        return redirect()->route('seller.dashboard')->with('success', 'Listing deleted.');
    }

    /**
     * A ticket available for purchase.
     */
    public function show(Ticket $ticket): View
    {
        abort_unless($ticket->isAvailable(), 404);

        return view('tickets.show', ['ticket' => $ticket]);
    }

    /**
     * A ticket the logged-in user has bought, with its QR code.
     */
    public function showPurchasedTicket(Ticket $ticket): View
    {
        $this->authorize('viewPurchased', $ticket);

        $qrCode = QrCode::size(250)->generate($ticket->validation_code);

        return view('tickets.purchased_show', [
            'ticket' => $ticket,
            'qrCode' => $qrCode,
        ]);
    }

    /**
     * Stream a proof of purchase to the seller or an admin.
     */
    public function showProof(Ticket $ticket): StreamedResponse
    {
        $this->authorize('viewProof', $ticket);

        abort_unless($ticket->purchase_proof, 404);

        // Older uploads were stored on the public disk; fall back to it.
        foreach ([self::PROOF_DISK, 'public'] as $disk) {
            if (Storage::disk($disk)->exists($ticket->purchase_proof)) {
                return Storage::disk($disk)->response($ticket->purchase_proof);
            }
        }

        abort(404);
    }

    /**
     * Delete a ticket together with its stored files.
     */
    public static function deleteWithFiles(Ticket $ticket): void
    {
        if ($ticket->purchase_proof) {
            Storage::disk(self::PROOF_DISK)->delete($ticket->purchase_proof);
            Storage::disk('public')->delete($ticket->purchase_proof);
        }

        $ticket->delete();
    }
}
