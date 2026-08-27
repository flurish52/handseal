<?php

namespace App\Observers;


use App\Models\Business;
use Illuminate\Support\Facades\DB;

class BusinessObserver
{
    public function creating(Business $business): void
    {
        $business->sequence_number = DB::transaction(function () {
            $max = Business::lockForUpdate()->max('sequence_number');
            return ($max ?? 0) + 1;
        });
    }
}
