<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'programme_id',
        'name',
        'enrollment_number',
        'phone',
        'start_at',
        'end_at',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'start_at' => 'date',
        'end_at' => 'date',
        'completed_at' => 'date',

    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function latestCertificate(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Certificate::class)->latestOfMany();
    }

    protected function isCertificateEligible(): Attribute
    {
        return Attribute::get(fn () => $this->completed_at !== null
            || ($this->end_at !== null && $this->end_at->isPast()));
    }

    protected $appends = ['is_certificate_eligible'];

    protected static function booted(): void
    {
        static::created(function (Student $student) {
            if ($student->enrollment_number) {
                return;
            }

            $prefix = config('handseal.cert_prefix');
            $initials = $student->business->initials();
            $businessId = $student->business_id;
            $studentId = str_pad((string) $student->id, config('handseal.cert_id_pad_length'), '0', STR_PAD_LEFT);

            $student->enrollment_number = "{$prefix}-{$initials}{$businessId}-{$studentId}";
            $student->saveQuietly();
        });
    }
}
