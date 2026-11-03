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

    public function isSold(): bool
    {
        return $this->status === self::STATUS_SOLD;
    }
}
