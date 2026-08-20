<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Certificate numbering
    |--------------------------------------------------------------------------
    | Format: {PREFIX}-{INITIALS}{BUSINESS_ID}-{CERT_ID}
    | e.g. HS-PFT7-000042
    */
    'cert_prefix' => 'HS',
    'cert_id_pad_length' => 6,       // zero-pad CERT_ID to this many digits
    'cert_initials_max_words' => 3,  // max words of business_name used for initials

    /*
    |--------------------------------------------------------------------------
    | QR codes
    |--------------------------------------------------------------------------
    */
    'qr_disk' => 'public',
    'qr_path' => 'qrcodes',          // storage/app/public/qrcodes
    'qr_size' => 300,                // px

    /*
    |--------------------------------------------------------------------------
    | Public verification
    |--------------------------------------------------------------------------
    | Base path for the public verify-by-certificate-number page.
    | Full URL built as: config('app.url') . '/' . verify_path . '/' . certificate_number
    */
    'verify_path' => 'verify',

    /*
    |--------------------------------------------------------------------------
    | Certificate templates
    |--------------------------------------------------------------------------
    */
    'builtin_template_count' => 5,

    /*
    |--------------------------------------------------------------------------
    | Referrals
    |--------------------------------------------------------------------------
    */
    'referral_reward_percent' => 25,

    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */
    'onboarding_fee_kobo' => env('HANDSEAL_ONBOARDING_FEE_KOBO', 200000),
    'free_certificates_before_onboarding' => 1,
    'free_certificates_after_onboarding' => 10,
    'pay_as_you_go_kobo' => 20000, // ₦200
    'subscription_monthly_kobo' => 100000, // ₦1000 monthly
];
