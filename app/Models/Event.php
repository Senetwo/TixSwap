<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * These fields must match what the TicketController is trying to create.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'location',
        'date',
        'image_url',
        'original_vendor',
    ];

    /**
     * Get the tickets for the event.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
