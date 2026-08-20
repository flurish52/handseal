<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'business_name',
        'address',
        'trade_category',
        'is_publicly_visible',
        'subscription_active_until',
        'logo_path'
    ];

    protected $casts = [
        'is_publicly_visible' => 'boolean',
        'subscription_active_until' => 'datetime',
        'owner_id' => 'integer',
    ];

    protected $appends = ['logo_url']; // merge with any existing $appends

    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->logo_path
                ? \Storage::disk('public')->url($this->logo_path)
                : null,
        );
    }
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function programmes(): HasMany
    {
        return $this->hasMany(Programme::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function certificateTemplates(): HasMany
    {
        return $this->hasMany(CertificateTemplate::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }


    public function certificateTemplateRequests()
    {
        return $this->hasMany(CertificateTemplateRequest::class);
    }

    /**
     * First letters of up to 3 words in business_name, alpha-only, uppercase.
     * Used to build the readable prefix of certificate_number.
     */
    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->business_name));
        $words = array_slice($words, 0, (int)config('handseal.cert_initials_max_words'));

        $letters = array_map(
            fn($word) => strtoupper(preg_replace('/[^A-Za-z]/', '', $word)[0] ?? ''),
            $words
        );

        return implode('', array_filter($letters));
    }

    public function hasPaidOnboarding(): bool
    {
        return $this->payments()
            ->where('type', 'onboarding')
            ->where('status', 'successful')
            ->exists();
    }

    public function onboardingPayment(): ?\App\Models\Payment
    {
        return $this->payments()->where('type', 'onboarding')
            ->where('status', 'successful')
            ->first();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription_active_until && $this->subscription_active_until->isFuture();
    }


}
