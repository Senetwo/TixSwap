<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    // Payment lifecycle
    public const STATUS_PENDING = 'pending';         // waiting for the buyer to approve the STK prompt
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';           // cancelled, wrong PIN, insufficient funds...
    public const STATUS_EXPIRED = 'expired';         // reservation ran out before payment
    public const STATUS_NEEDS_REFUND = 'needs_refund'; // money received but the ticket could not be delivered
    public const STATUS_REFUNDED = 'refunded';

    // Escrow lifecycle (only for paid orders)
    public const ESCROW_HELD = 'held';
    public const ESCROW_RELEASED = 'released';
    public const ESCROW_REFUNDED = 'refunded';
    public const ESCROW_DISPUTED = 'disputed'; // frozen until an admin decides

    /**
     * Only the buyer and ticket are set from controller input; every money
     * and status field is set explicitly with forceFill() in CheckoutService.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'ticket_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'commission' => 'decimal:2',
            'seller_amount' => 'decimal:2',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'escrow_release_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Every STK push attempt for this order.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * The latest STK push attempt.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function payout(): HasOne
    {
        return $this->hasOne(Payout::class);
    }

    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class);
    }

    /**
     * The buyer can still report a problem: paid, money still in escrow,
     * and no dispute opened yet.
     */
    public function canBeDisputed(): bool
    {
        return $this->isPaid()
            && $this->escrow_status === self::ESCROW_HELD
            && ! $this->dispute()->exists();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }
}
