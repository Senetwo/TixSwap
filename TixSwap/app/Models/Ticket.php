<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * This now matches your database schema and controller.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'user_id', // Correctly uses user_id for the seller
        'price',
        'seat_details',
        'status',
        'original_vendor',
        'purchase_proof',
        'buyer_id', 
        'validation_code',
    ];

    /**
     * Get the event that this ticket belongs to.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the seller who listed the ticket.
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
