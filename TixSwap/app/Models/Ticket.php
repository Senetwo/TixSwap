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

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_APPROVED && $this->buyer_id === null;
    }

    public function isSold(): bool
    {
        return $this->status === self::STATUS_SOLD;
    }
}
