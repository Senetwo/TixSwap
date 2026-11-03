<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'location',
        'date',
        'image_url',
        'original_vendor',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
        ];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Tickets a buyer could purchase right now.
     */
    public function availableTickets(): HasMany
    {
        return $this->tickets()->where('status', Ticket::STATUS_APPROVED)->whereNull('buyer_id');
    }

    /**
     * Upcoming events that have at least one ticket on sale.
     */
    public function scopeOnSale(Builder $query): Builder
    {
        return $query->where('date', '>', now())->whereHas('availableTickets');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! filled($term)) {
            return $query;
        }

        return $query->where(fn ($q) => $q->where('name', 'like', '%'.$term.'%')->orWhere('location', 'like', '%'.$term.'%'));
    }

    public function isPast(): bool
    {
        return $this->date->isPast();
    }

    /**
     * URL-friendly name used in links (purely cosmetic; the id is what's looked up).
     */
    public function slug(): string
    {
        return Str::slug($this->name) ?: 'event';
    }

    /**
     * Find an existing event that is clearly the same one: same name
     * (ignoring case, spacing and punctuation) on the same day.
     */
    public static function findMatching(string $name, Carbon|string $date): ?self
    {
        $day = Carbon::parse($date);
        $key = static::nameKey($name);

        return static::query()
            ->whereBetween('date', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])
            ->get()
            ->first(fn (self $e) => static::nameKey($e->name) === $key);
    }

    public static function nameKey(string $name): string
    {
        // "Blankets and Wine" == "Blankets & Wine" == "blankets-&-wine!"
        $name = preg_replace('/\band\b/', '&', Str::lower(Str::ascii($name)));

        return preg_replace('/[^a-z0-9]/', '', $name);
    }
}
