<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PaymentController extends Controller
{
    /**
     * Process a (still simulated) payment for a ticket.
     *
     * The ticket row is locked for the duration of the transaction so two
     * buyers paying at the same moment cannot both end up owning it.
     */
    public function processSimulatedPayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ticket_id' => 'required|integer|exists:tickets,id',
            'phone_number' => ['required', 'string', 'regex:/^(\+?254|0)?[17]\d{8}$/'],
        ], [
            'phone_number.regex' => 'Enter a valid Kenyan phone number, e.g. 0712345678.',
        ]);

        $buyer = $request->user();

        try {
            $ticket = DB::transaction(function () use ($validated, $buyer) {
                $ticket = Ticket::whereKey($validated['ticket_id'])->lockForUpdate()->firstOrFail();

                if ($buyer->cannot('purchase', $ticket)) {
                    return null;
                }

                $ticket->forceFill([
                    'status' => Ticket::STATUS_SOLD,
                    'buyer_id' => $buyer->id,
                    'validation_code' => (string) Str::uuid(),
                ])->save();

                return $ticket;
            });
        } catch (\Throwable $e) {
            Log::error('Payment failed', ['ticket_id' => $validated['ticket_id'], 'error' => $e->getMessage()]);

            return back()->with('error', 'An error occurred during payment. Please try again.');
        }

        if ($ticket === null) {
            return redirect()->route('dashboard')
                ->with('error', 'Sorry, this ticket is no longer available.');
        }

        return redirect()->route('payment.success', ['ticket' => $ticket->id]);
    }

    /**
     * Show the payment success page with the ticket and its QR code.
     */
    public function paymentSuccess(Ticket $ticket): View
    {
        $this->authorize('viewPurchased', $ticket);

        // The QR code carries the ticket's unique validation code, which is
        // what a gate scanner checks.
        $qrCode = QrCode::size(250)->generate($ticket->validation_code);

        return view('payment.success', [
            'ticket' => $ticket,
            'qrCode' => $qrCode,
        ]);
    }
}
