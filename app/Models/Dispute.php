<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispute extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_REFUNDED = 'refunded'; // decided for the buyer
    public const STATUS_RELEASED = 'released'; // decided for the seller

    /**
     * Reasons a buyer can choose, with the wording shown to people.
     */
    public const REASONS = [
        'rejected_at_entry' => 'The ticket was rejected at the entrance',
        'already_used' => 'The ticket had already been used or sold to someone else',
        'wrong_ticket' => 'Wrong event, date, seat or ticket type',
        'event_cancelled' => 'The event was cancelled or postponed',
        'not_received' => 'I did not receive a usable ticket',
        'other' => 'Something else',
    ];

    /** @var array<int, string> */
    protected $fillable = [];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? $this->reason;
    }
}
