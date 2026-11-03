<?php

namespace App\Services\ProofCheck;

use App\Models\Event;
use App\Models\ProofCheck;
use App\Models\Ticket;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Reads a listing's proof of purchase and compares it with what the
 * seller entered. The comparison is plain code (not the model), so it's
 * predictable; the result only ever informs a human reviewer.
 */
class ProofCheckService
{
    public function __construct(private readonly ?ProofReader $reader) {}

    public static function enabled(): bool
    {
        return in_array(config('services.ai_proof.driver'), ['claude', 'fake'], true);
    }

    public function check(Ticket $ticket): ?ProofCheck
    {
        if (! $this->reader || ! $ticket->purchase_proof) {
            return null;
        }

        $check = ProofCheck::where('ticket_id', $ticket->id)->first() ?? new ProofCheck;
        $check->forceFill(['ticket_id' => $ticket->id, 'status' => ProofCheck::STATUS_PENDING, 'error' => null, 'model' => $this->reader->name()])->save();

        [$contents, $mime] = $this->loadProof($ticket);

        if ($contents === null) {
            $check->forceFill(['status' => ProofCheck::STATUS_FAILED, 'error' => 'Proof file not found.'])->save();

            return $check;
        }

        try {
            $reading = $this->reader->read($contents, $mime);
        } catch (ProofReadException $e) {
            Log::warning('Proof check failed', ['ticket_id' => $ticket->id, 'error' => $e->getMessage()]);
            $check->forceFill(['status' => ProofCheck::STATUS_FAILED, 'error' => Str::limit($e->getMessage(), 250)])->save();

            return $check;
        }

        [$verdict, $findings] = $this->compare($ticket->loadMissing('event'), $reading);

        $check->forceFill([
            'status' => ProofCheck::STATUS_DONE,
            'verdict' => $verdict,
            'extracted' => $reading->toArray(),
            'findings' => $findings,
        ])->save();

        return $check;
    }

    /**
     * @return array{0: string, 1: list<array{level: string, text: string}>}
     */
    public function compare(Ticket $ticket, ProofReading $r): array
    {
        if (! $r->readable) {
            return [ProofCheck::VERDICT_UNREADABLE, [['level' => 'warning', 'text' => 'The proof couldn\'t be read clearly.']]];
        }

        $findings = [];
        // By-reference closures: arrow functions (fn) would only append to a copy.
        $warn = function (string $text) use (&$findings) { $findings[] = ['level' => 'warning', 'text' => $text]; };
        $ok = function (string $text) use (&$findings) { $findings[] = ['level' => 'ok', 'text' => $text]; };

        if (! $r->isTicketOrReceipt) {
            $warn('This doesn\'t look like a ticket or receipt.');
        }

        // Event name: same name key, or one contains the other.
        if ($r->eventName) {
            $a = Event::nameKey($r->eventName);
            $b = Event::nameKey($ticket->event->name);
            ($a !== '' && (str_contains($a, $b) || str_contains($b, $a)))
                ? $ok('Event name matches ("'.$r->eventName.'").')
                : $warn('Event name on proof is "'.$r->eventName.'", listing says "'.$ticket->event->name.'".');
        }

        // Date: same calendar day.
        if ($r->eventDate) {
            try {
                Carbon::parse($r->eventDate)->isSameDay($ticket->event->date)
                    ? $ok('Event date matches.')
                    : $warn('Date on proof is '.Carbon::parse($r->eventDate)->format('D j M Y').', listing says '.$ticket->event->date->format('D j M Y').'.');
            } catch (\Throwable) {
                // Unparseable date: say nothing rather than a false alarm.
            }
        }

        // Price paid vs the face value the seller entered (5% tolerance for fees/rounding).
        if ($r->pricePaid && $ticket->face_value) {
            abs($r->pricePaid - (float) $ticket->face_value) <= max(50, (float) $ticket->face_value * 0.05)
                ? $ok('Price on proof matches the face value (KES '.number_format($r->pricePaid).').')
                : $warn('Price on proof is '.($r->currency ?? 'KES').' '.number_format($r->pricePaid).', seller entered face value KES '.number_format($ticket->face_value).'.');
        }

        // Ticket/order number, when the seller gave one.
        if ($r->orderOrTicketNumber && $ticket->ticket_reference) {
            Ticket::normaliseReference($r->orderOrTicketNumber) === $ticket->ticket_reference
                ? $ok('Order/ticket number matches.')
                : $warn('Number on proof is "'.$r->orderOrTicketNumber.'", seller entered "'.$ticket->ticket_reference.'".');
        }

        // Seat/ticket type: loose match, only flag a clear difference.
        if ($r->seatOrTicketType) {
            $proof = Str::lower($r->seatOrTicketType);
            $listing = Str::lower($ticket->seat_details);
            similar_text($proof, $listing, $pct);
            (str_contains($proof, $listing) || str_contains($listing, $proof) || $pct >= 60)
                ? $ok('Seat/ticket type matches.')
                : $warn('Proof shows "'.$r->seatOrTicketType.'", listing says "'.$ticket->seat_details.'".');
        }

        foreach ($r->editSigns as $sign) {
            $warn('Possible editing: '.$sign);
        }

        $hasWarnings = collect($findings)->contains('level', 'warning');

        return [$hasWarnings ? ProofCheck::VERDICT_NEEDS_ATTENTION : ProofCheck::VERDICT_LOOKS_GOOD, array_values($findings)];
    }

    /**
     * @return array{0: ?string, 1: string}
     */
    private function loadProof(Ticket $ticket): array
    {
        foreach (['local', 'public'] as $disk) {
            $storage = Storage::disk($disk);
            if ($storage->exists($ticket->purchase_proof)) {
                $contents = $storage->get($ticket->purchase_proof);
                $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents) ?: 'image/jpeg';

                return [$contents, in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'], true) ? $mime : 'image/jpeg'];
            }
        }

        return [null, ''];
    }
}
