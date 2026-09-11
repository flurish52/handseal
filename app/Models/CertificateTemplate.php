<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificateTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'content',
        'status',
        'source',
        'certificate_template_request_id',
        'is_premium',
        'price',
        'thumbnail_path',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'business_id' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(TemplatePurchase::class);
    }

    public static function defaultBuiltin(): self
    {
        return static::where('source', 'builtin')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->firstOrFail();
    }
}
