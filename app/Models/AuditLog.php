<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * An append-only record of admin actions.
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    /**
     * Human wording for each action, used on the activity page.
     */
    public const ACTIONS = [
        'listing.approved' => 'approved a listing',
        'listing.rejected' => 'rejected a listing',
        'listing.updated' => 'edited a listing',
        'listing.deleted' => 'deleted a listing',
        'user.deleted' => 'deleted a user',
        'dispute.refunded' => 'decided a dispute for the buyer',
        'dispute.released' => 'decided a dispute for the seller',
        'payout.paid' => 'recorded a payout',
        'order.refunded' => 'recorded a refund',
    ];

    /** @var array<int, string> */
    protected $fillable = [];

    protected function casts(): array
    {
        return ['details' => 'array', 'created_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function record(string $action, ?Model $subject = null, array $details = []): self
    {
        $log = new self;
        $log->forceFill([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'details' => $details ?: null,
            'ip_address' => request()?->ip(),
        ])->save();

        return $log;
    }

    public function description(): string
    {
        return self::ACTIONS[$this->action] ?? $this->action;
    }
}
