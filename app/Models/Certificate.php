<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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
        'public_verification_number',
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
        // Format: {PREFIX}-{BUSINESS_SEQUENCE}-{LOCAL_NUMBER}, e.g. STACKP-000001-000001
        // local_number is scoped per business, assigned before certificate_number so it exists here.
        static::creating(function (Certificate $certificate) {
            $max = static::where('business_id', $certificate->business_id)
                ->lockForUpdate()
                ->max('local_number');

            $certificate->local_number = ($max ?? 0) + 1;
        });

        static::created(function (Certificate $certificate) {
            $dirty = false;

            if (! $certificate->certificate_number) {
                $certPrefix = $certificate->business->certPrefix();
                $businessSeq = str_pad((string) $certificate->business->sequence_number, config('handseal.cert_id_pad_length'), '0', STR_PAD_LEFT);
                $localNum = str_pad((string) $certificate->local_number, config('handseal.cert_id_pad_length'), '0', STR_PAD_LEFT);

                $certificate->certificate_number = "{$certPrefix}-{$businessSeq}-{$localNum}";
                $dirty = true;
            }

            if (! $certificate->public_verification_number) {
                $certificate->public_verification_number = static::generateUniquePublicNumber();
                $dirty = true;
            }

            if ($dirty) {
                $certificate->saveQuietly();
            }
        });
    }

    protected static function generateUniquePublicNumber(): string
    {
        do {
            $code = strtolower(Str::random(16));
        } while (static::where('public_verification_number', $code)->exists());

        return $code;
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
