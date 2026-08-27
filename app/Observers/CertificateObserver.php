<?php

namespace App\Observers;


use App\Models\Certificate;
use Illuminate\Support\Facades\DB;

class CertificateObserver
{
    public function creating(Certificate $certificate): void
    {
        $certificate->local_number = DB::transaction(function () use ($certificate) {
            $max = Certificate::where('business_id', $certificate->business_id)
                ->lockForUpdate()
                ->max('local_number');
            return ($max ?? 0) + 1;
        });
    }
}
