<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'included_certs',
        'rollover_cap_multiplier',
        'extra_cert_price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'integer',
        'included_certs' => 'integer',
        'extra_cert_price' => 'integer',
    ];

    // ADDED: so `price_naira` rides along automatically whenever a Plan is
    // serialized to the frontend (e.g. Inertia props), without every
    // controller having to remember to append it manually.
    protected $appends = ['price_naira'];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isUnlimited(): bool
    {
        return is_null($this->included_certs);
    }

    /** Price formatted as Naira, e.g. ₦1,000 */
    public function getPriceNairaAttribute(): string
    {
        return '₦' . number_format($this->price / 100, 0);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
