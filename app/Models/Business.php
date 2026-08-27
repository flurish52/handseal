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
        'logo_path',
        'cert_prefix',
        'ai_rejection_count',
        'ai_attempts_remaining',
        'default_builtin_template_key',
    ];

    protected $casts = [
        'is_publicly_visible' => 'boolean',
        'subscription_active_until' => 'datetime',
        'owner_id' => 'integer',
        'ai_attempts_remaining' => 'integer',
        'ai_rejection_count' => 'integer',
    ];

    protected $appends = ['logo_url', 'initials'];


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

    /**
     * Full prefix used in certificate_number. Custom prefix replaces the
     * "HS-{initials}" default entirely once set.
     */
    public function certPrefix(): string
    {
        return $this->cert_prefix ?: 'HS-' . $this->initials();
    }


    public function hasActiveSubscription(): bool
    {
        return $this->subscription_active_until && $this->subscription_active_until->isFuture();
    }

    public function wallet(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Wallet::class);
    }
    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->with('plan')
            ->where('status', 'active')
            ->where('current_period_ends_at', '>', now())
            ->latest('current_period_ends_at')
            ->first();
    }

    public function hasActiveCustomTemplate(): bool
    {
        return $this->certificateTemplates()->where('status', 'active')->exists();
    }

    public function canGenerateAiForFree(): bool
    {
        return ! $this->hasActiveCustomTemplate() && $this->ai_attempts_remaining > 0;
    }


    public function unusedTemplateRequestVoucher(): ?\App\Models\Payment
    {
        return $this->payments()
            ->where('type', 'template_fee')
            ->where('status', 'successful')
            ->whereNull('used_at')
            ->get()
            ->first(fn ($payment) => ($payment->metadata['purpose'] ?? null) === 'team_request');
    }

    public function canRequestFromAdmins(): bool
    {
        return (bool) $this->unusedTemplateRequestVoucher();
    }

    public function setCertPrefixAttribute($value): void
    {
        $this->attributes['cert_prefix'] = $value ? strtoupper($value) : null;
    }
    public function getInitialsAttribute(): string
    {
        return $this->initials(); // or inline the same logic if initials() itself doesn't exist as a separate method
    }

    /**
     * Single source of truth for "what template does this business's cert
     * use right now" — active custom template wins, else their chosen
     * builtin, else a hard fallback. Nothing outside this method should
     * ever read builtin_template_key/certificate_template_id off a request.
     */
    public function resolvedTemplateSelection(): array
    {
        $active = $this->certificateTemplates()->where('status', 'active')->first();

        if ($active) {
            return ['builtin_template_key' => null, 'certificate_template_id' => $active->id];
        }

        return [
            'builtin_template_key' => $this->default_builtin_template_key ?: 'classic-navy',
            'certificate_template_id' => null,
        ];
    }



}
