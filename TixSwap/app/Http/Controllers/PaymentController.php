<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Instantly processes a simulated payment.
     */
    public function processSimulatedPayment(Request $request)
    {
        // Corrected validation rule to min:9 for the phone number
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'phone_number' => 'required|string|min:9',
        ]);

        $ticket = Ticket::findOrFail($request->ticket_id);

        // Restored the try...catch block for robust error handling
        DB::beginTransaction();
        try {
            $ticket->update([
                'status' => 'sold',
                'buyer_id' => Auth::id(),
                'validation_code' => Str::uuid(),
            ]);

            DB::commit();

            return redirect()->route('payment.success', ['ticket' => $ticket->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred during payment. Please try again.');
        }
    }

    /**
     * Display the payment success page with ticket and QR code.
     */
    public function paymentSuccess(Ticket $ticket): View
    {
        // TODO: Re-enable this security check before going live!
        // abort_if($ticket->buyer_id !== Auth::id(), 403);

        $qrCodeData = route('tickets.purchased.show', $ticket);
        $qrCode = QrCode::size(250)->generate($qrCodeData);

        return view('payment.success', [
            'ticket' => $ticket,
            'qrCode' => $qrCode,
        ]);
    }
}
