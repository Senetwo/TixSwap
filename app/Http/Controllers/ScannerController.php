<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Entrance scanning. Each valid ticket can be admitted exactly once.
 */
class ScannerController extends Controller
{
    public function index(Request $request): View
    {
        return view('scanner.index', [
            // Events happening around now, to restrict scanning to one gate.
            'events' => Event::whereBetween('date', [now()->subDay(), now()->addDays(2)])->orderBy('date')->get(),
            'eventId' => $request->integer('event') ?: null,
        ]);
    }

    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100',
            'event_id' => 'nullable|integer',
        ]);

        // QR codes hold the validation code; tolerate stray spaces/case.
        $code = strtolower(trim($validated['code']));

        return DB::transaction(function () use ($code, $validated, $request) {
            $ticket = Ticket::with(['event', 'buyer', 'checkedInBy'])
                ->where('validation_code', $code)
                ->lockForUpdate()
                ->first();

            if (! $ticket || $ticket->status !== Ticket::STATUS_SOLD) {
                return $this->result('invalid', 'Not a valid TixSwap ticket.');
            }

            if (! empty($validated['event_id']) && $ticket->event_id !== (int) $validated['event_id']) {
                return $this->result('wrong_event', 'This ticket is for a different event.', $ticket);
            }

            // A refunded or disputed sale shouldn't get in.
            $order = Order::where('ticket_id', $ticket->id)->where('user_id', $ticket->buyer_id)->latest('id')->with('dispute')->first();
            if ($order && in_array($order->status, [Order::STATUS_REFUNDED, Order::STATUS_NEEDS_REFUND], true)) {
                return $this->result('blocked', 'This ticket was refunded and is no longer valid.', $ticket);
            }

            if ($ticket->checked_in_at) {
                return $this->result('already_used', 'Already scanned at '.$ticket->checked_in_at->format('g:i A')
                    .($ticket->checkedInBy ? ' by '.$ticket->checkedInBy->name : '').'.', $ticket);
            }

            $ticket->forceFill(['checked_in_at' => now(), 'checked_in_by' => $request->user()->id])->save();

            return $this->result('admitted', 'Valid ticket. Let them in.', $ticket);
        });
    }

    private function result(string $result, string $message, ?Ticket $ticket = null): JsonResponse
    {
        return response()->json([
            'result' => $result,
            'message' => $message,
            'ticket' => $ticket ? [
                'event' => $ticket->event?->name,
                'seat' => $ticket->seat_details,
                'holder' => $ticket->buyer?->name,
            ] : null,
        ]);
    }
}
