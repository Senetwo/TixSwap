<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProofCheck extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_DONE = 'done';
    public const STATUS_FAILED = 'failed';

    public const VERDICT_LOOKS_GOOD = 'looks_good';
    public const VERDICT_NEEDS_ATTENTION = 'needs_attention';
    public const VERDICT_UNREADABLE = 'unreadable';

    /** @var array<int, string> */
    protected $fillable = [];

    protected function casts(): array
    {
        return ['extracted' => 'array', 'findings' => 'array'];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
