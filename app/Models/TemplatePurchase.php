<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplatePurchase extends Model
{
    protected $fillable = [
        'business_id',
        'certificate_template_id',
        'price_paid',
        'payment_method',
        'purchased_at',
    ];
}
