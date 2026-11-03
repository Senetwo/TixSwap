<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'payout_phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    /** @var array<string, int|null>|null */
    private ?array $sellerStatsCache = null;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Phone number for SMS: the payout number, or the number they last paid with.
     */
    public function routeNotificationForSms(): ?string
    {
        return $this->payout_phone ?: $this->orders()->whereNotNull('phone_number')->latest('id')->value('phone_number');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSeller(): bool
    {
        return $this->role === 'seller';
    }

    /** Gate staff who scan tickets at the entrance. */
    public function isScanner(): bool
    {
        return $this->role === 'scanner';
    }

    public function canScanTickets(): bool
    {
        return $this->isScanner() || $this->isAdmin();
    }

    public function isBuyer(): bool
    {
        return $this->role === 'buyer';
    }

    /**
     * Get the tickets for the user (seller).
     */
    public function tickets(): HasMany
    {
        // This defines that a User has many Tickets, using the 'user_id' column as the foreign key.
         return $this->hasMany(\App\Models\Ticket::class, 'user_id');
    }

    /**
     * Get the tickets purchased by the user (buyer).
     */
    public function purchasedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'buyer_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function ratingsReceived(): HasMany
    {
        return $this->hasMany(SellerRating::class, 'seller_id');
    }

    /**
     * Seller track record, cached per request.
     *
     * @return array{completed: int, refunded: int, positive: int, ratings: int, percent: ?int}
     */
    public function sellerStats(): array
    {
        return $this->sellerStatsCache ??= (function () {
            $sold = Order::whereHas('ticket', fn ($q) => $q->where('user_id', $this->id));
            $positive = $this->ratingsReceived()->where('positive', true)->count();
            $ratings = $this->ratingsReceived()->count();

            return [
                // Sales whose money has been released to the seller.
                'completed' => (clone $sold)->where('escrow_status', Order::ESCROW_RELEASED)->count(),
                // Sales refunded because the buyer's dispute was upheld.
                'refunded' => (clone $sold)->whereHas('dispute', fn ($q) => $q->where('status', Dispute::STATUS_REFUNDED))->count(),
                'positive' => $positive,
                'ratings' => $ratings,
                'percent' => $ratings ? (int) round($positive / $ratings * 100) : null,
            ];
        })();
    }

    /**
     * Fewer than N completed sales: their payouts are held longer.
     */
    public function isNewSeller(): bool
    {
        return $this->sellerStats()['completed'] < (int) config('services.tixswap.trusted_seller_sales', 3);
    }

    /**
     * Enough completed sales, no upheld disputes, and mostly positive ratings.
     */
    public function isTrustedSeller(): bool
    {
        $stats = $this->sellerStats();

        return ! $this->isNewSeller()
            && $stats['refunded'] === 0
            && ($stats['percent'] === null || $stats['percent'] >= 90);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class, 'seller_id');
    }
}