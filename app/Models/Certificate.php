<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'student_id',
        'certificate_template_id',
        'builtin_template_key',
        'programme_id',
        'certificate_number',
        'recipient_name',
        'start_date',
        'end_date',
        'is_guest',
        'qr_path',
        'issued_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'issued_at' => 'datetime',
        'is_guest' => 'boolean',
        'business_id' => 'integer',
        'student_id' => 'integer',
        'certificate_template_id' => 'integer',
        'programme_id' => 'integer',
    ];

    protected static function booted(): void
    {
        // Format: {PREFIX}-{INITIALS}{BUSINESS_ID}-{CERT_ID}, e.g. HS-PFT7-000042
        // Runs after insert so the auto-increment id (CERT_ID, the actual uniqueness guarantee) exists.
        static::created(function (Certificate $certificate) {
            if ($certificate->certificate_number) {
                return;
            }

            $certPrefix = $certificate->business->certPrefix(); // "JBC" or "HS-PFT" fallback
            $businessId = $certificate->business_id;
            $certId = str_pad((string) $certificate->id, config('handseal.cert_id_pad_length'), '0', STR_PAD_LEFT);

            $certificate->certificate_number = "{$certPrefix}{$businessId}-{$certId}";
            $certificate->saveQuietly();
        });;
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function certificateTemplate(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class);
    }

    /**
     * Resolves which Blade view renders this certificate — a custom template's stored
     * content, or one of the 5 built-in preset views keyed by builtin_template_key.
     */
    /**
     * DomPDF needs images embedded, not linked — reads the stored QR PNG and
     * returns it as a base64 data URI ready to drop straight into an <img src>.
     */
    public function qrBase64(): ?string
    {
        if (! $this->qr_path || ! \Storage::disk(config('handseal.qr_disk'))->exists($this->qr_path)) {
            return null;
        }

        $contents = \Storage::disk(config('handseal.qr_disk'))->get($this->qr_path);

        return 'data:image/png;base64,' . base64_encode($contents);
    }

    public function resolveTemplateContent(): string
    {
        if ($this->certificate_template_id) {
            return $this->certificateTemplate->content;
        }

        return view("certificates.builtin.{$this->builtin_template_key}")->render();
    }
}
