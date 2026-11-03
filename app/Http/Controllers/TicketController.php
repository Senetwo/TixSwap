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
        $proofHash = hash_file('sha256', $request->file('purchase_proof')->getRealPath());

        // The exact same proof file already used by a different seller is
        // a strong sign of a copied receipt: refuse the listing.
        $copiedFromSomeoneElse = Ticket::where('proof_hash', $proofHash)
            ->where('user_id', '!=', Auth::id())
            ->where('status', '!=', Ticket::STATUS_REJECTED)
            ->exists();

        if ($copiedFromSomeoneElse) {
            Log::warning('Listing blocked: proof of purchase already used by another seller', ['user_id' => Auth::id(), 'proof_hash' => $proofHash]);

            return back()->withInput()->withErrors([
                'purchase_proof' => 'This proof of purchase has already been used for another listing. If you think this is a mistake, contact support@tixswap.com.',
            ]);
        }

        try {
            // Reuse the event if it's already on TixSwap (picked from the
            // suggestions, or same name on the same day), so all its
            // tickets appear together on one event page.
            $existingEvent = isset($validated['event_id'])
                ? Event::find($validated['event_id'])
                : Event::findMatching($validated['event_name'], $validated['date']);

            $eventImagePath = $request->hasFile('event_image') && ! ($existingEvent?->image_url && Storage::disk('public')->exists($existingEvent->image_url))
                ? $request->file('event_image')->store('event_images', 'public')
                : null;
            $purchaseProofPath = $request->file('purchase_proof')->store('purchase_proofs', self::PROOF_DISK);

            $ticket = DB::transaction(function () use ($validated, $eventImagePath, $purchaseProofPath, $proofHash, $existingEvent) {
                if ($existingEvent) {
                    $event = $existingEvent;
                    if ($eventImagePath) {
                        $event->update(['image_url' => $eventImagePath]);
                    }
                } else {
                    $event = Event::create([
                        'name' => $validated['event_name'],
                        'location' => $validated['location'],
                        'date' => $validated['date'],
                        'image_url' => $eventImagePath,
                        'original_vendor' => $validated['original_vendor'] ?? 'N/A',
                    ]);
                }

                $ticket = new Ticket([
                    'event_id' => $event->id,
                    'user_id' => Auth::id(),
                    'price' => $validated['price'],
                    'face_value' => $validated['face_value'],
                    'seat_details' => $validated['seat_details'],
                    'original_vendor' => $validated['original_vendor'] ?? 'N/A',
                    'purchase_proof' => $purchaseProofPath,
                ]);
                $ticket->forceFill([
                    'status' => Ticket::STATUS_PENDING,
                    'proof_hash' => $proofHash,
                    'ticket_reference' => Ticket::normaliseReference($validated['ticket_reference'] ?? null),
                ])->save();

                return $ticket;
            });

            // Read the proof with AI (if switched on) once the seller has their response.
            if (\App\Services\ProofCheck\ProofCheckService::enabled()) {
                $ticketId = $ticket->id;
                dispatch(function () use ($ticketId) {
                    if ($t = Ticket::find($ticketId)) {
                        app(\App\Services\ProofCheck\ProofCheckService::class)->check($t);
                    }
                })->afterResponse();
            }

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

    /**
     * Upcoming events matching what the seller is typing, so they can
     * attach their ticket to an event that's already listed.
     */
    public function searchEvents(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $term = trim((string) $request->query('q'));

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        return response()->json(
            Event::where('date', '>', now())
                ->where('name', 'like', '%'.$term.'%')
                ->orderBy('date')
                ->limit(6)
                ->get()
                ->map(fn (Event $e) => [
                    'id' => $e->id,
                    'name' => $e->name,
                    'location' => $e->location,
                    'date' => $e->date->format('Y-m-d\TH:i'),
                    'label' => $e->date->format('D j M Y, g:i A').' · '.$e->location,
                ])
        );
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
        // Show reserved tickets too, with a "someone is paying" notice.
        abort_unless($ticket->isAvailable() || $ticket->isReserved(), 404);

        return view('tickets.show', [
            'ticket' => $ticket,
            'reserved' => $ticket->isReserved(),
        ]);
    }

    /**
     * A ticket the logged-in user has bought, with its QR code.
     */
    public function showPurchasedTicket(Ticket $ticket): View
    {
        $this->authorize('viewPurchased', $ticket);

        $qrCode = QrCode::size(250)->generate($ticket->validation_code);

        // The paid order behind this ticket (none for tickets sold before M-Pesa checkout).
        $order = $ticket->orders()
            ->where('user_id', Auth::id())
            ->whereNotNull('paid_at')
            ->with('dispute')
            ->latest('id')
            ->first();

        return view('tickets.purchased_show', [
            'ticket' => $ticket,
            'qrCode' => $qrCode,
            'order' => $order,
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
