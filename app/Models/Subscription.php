<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id', 'plan_id', 'status',
        'started_at', 'current_period_ends_at', 'cancelled_at',
        'certs_used_this_period',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'current_period_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'certs_used_this_period' => 'integer',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Source of truth for "is this business a paying subscriber right now".
     * Use this everywhere you'd otherwise check a boolean flag directly —
     * referral validation, paywall checks, plan page state, etc.
     */
    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->current_period_ends_at->isFuture();
    }

    public function remainingQuota(): ?int
    {
        if ($this->plan->isUnlimited()) return null;
        return max(0, $this->plan->included_certs - $this->certs_used_this_period);
    }
}
