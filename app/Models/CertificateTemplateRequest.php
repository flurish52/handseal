<?php
// app/Models/CertificateTemplateRequest.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateTemplateRequest extends Model
{
    protected $fillable = [
        'business_id',
        'certificate_template_id',
        'name',
        'description',
        'sample_type',
        'images',
        'status',
        'admin_note',
        'reviewed_at',
    ];

    protected $casts = [
        'images' => 'array',
        'reviewed_at' => 'datetime',
        'business_id' => 'integer',
        'certificate_template_id' => 'integer',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function certificateTemplate()
    {
        return $this->belongsTo(CertificateTemplate::class);
    }
}
