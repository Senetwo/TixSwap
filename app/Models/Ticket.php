<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_RESERVED = 'reserved'; // held for a buyer while they approve the M-Pesa prompt
    public const STATUS_SOLD = 'sold';

    /**
     * The attributes that are mass assignable.
     *
     * Note: 'status', 'buyer_id' and 'validation_code' are deliberately NOT
     * mass assignable, so they can never be set straight from user input.
     * Set them explicitly with forceFill() in trusted code paths.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'user_id',
        'price',
        'face_value',
        'seat_details',
        'original_vendor',
        'purchase_proof',
    ];

    protected function casts(): array
    {
        return [
            'reserved_until' => 'datetime',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * The seller who listed the ticket.
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The buyer who purchased the ticket.
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Can be bought right now: approved and unsold, or held by a
     * reservation that has already run out.
     */
    public function isAvailable(): bool
    {
        if ($this->buyer_id !== null) {
            return false;
        }

        if ($this->status === self::STATUS_APPROVED) {
            return true;
        }

        return $this->status === self::STATUS_RESERVED && $this->reservationExpired();
    }

    public function isReserved(): bool
    {
        return $this->status === self::STATUS_RESERVED && ! $this->reservationExpired();
    }

    public function reservationExpired(): bool
    {
        return $this->reserved_until === null || $this->reserved_until->isPast();
    }

    /**
     * Normalise a ticket/order number so "ab-123 45" and "AB12345" match.
     */
    public static function normaliseReference(?string $reference): ?string
    {
        $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $reference));

        return $clean === '' ? null : $clean;
    }

    /**
     * Other listings that look like the same ticket: the same proof file
     * or the same ticket/order number. Rejected listings are ignored.
     *
     * @return \Illuminate\Support\Collection<int, array{ticket: Ticket, match: string, other_seller: bool}>
     */
    public function possibleDuplicates(): \Illuminate\Support\Collection
    {
        if (! $this->proof_hash && ! $this->ticket_reference) {
            return collect();
        }

        return static::query()
            ->with('seller')
            ->whereKeyNot($this->id)
            ->where('status', '!=', self::STATUS_REJECTED)
            ->where(function ($q) {
                if ($this->proof_hash) {
                    $q->orWhere('proof_hash', $this->proof_hash);
                }
                if ($this->ticket_reference) {
                    $q->orWhere('ticket_reference', $this->ticket_reference);
                }
            })
            ->get()
            ->map(fn (Ticket $other) => [
                'ticket' => $other,
                'match' => $this->proof_hash && $other->proof_hash === $this->proof_hash ? 'proof' : 'reference',
                'other_seller' => $other->user_id !== $this->user_id,
            ]);
    }

    /**
     * Highest price allowed for a given face value, or null if uncapped.
     */
    public static function maxPriceFor(float|int|string|null $faceValue): ?int
    {
        $cap = config('services.tixswap.max_markup_percent');

        if ($cap === null || ! $faceValue) {
            return null;
        }

        return (int) floor((float) $faceValue * (1 + $cap / 100));
    }

    /**
     * Price difference against face value, in whole percent (e.g. 15 or -10).
     */
    public function markupPercent(): ?int
    {
        if (! $this->face_value || (float) $this->face_value <= 0) {
            return null;
        }

        return (int) round(((float) $this->price / (float) $this->face_value - 1) * 100);
    }

    public function isSold(): bool
    {
        return $this->status === self::STATUS_SOLD;
    }
}
