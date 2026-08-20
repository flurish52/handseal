<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Inertia\Inertia;
use Inertia\Response;

class VerificationController extends Controller
{
    /**
     * Public — no auth. Lookup by certificate_number (typed in, or reached via QR scan,
     * both land here). Never expose anything beyond these 5 fields — no student phone,
     * no programme price, no business owner info, nothing else on the certificate row.
     */
    public function show(string $certificateNumber): Response
    {
        $certificate = Certificate::where('certificate_number', $certificateNumber)
            ->with('business:id,business_name', 'programme:id,name')
            ->first();

        return Inertia::render('Verify/Show', [
            'result' => $certificate ? [
                'valid' => true,
                'recipient_name' => $certificate->recipient_name,
                'programme_name' => $certificate->programme->name,
                'business_name' => $certificate->business->business_name,
                'issued_at' => $certificate->issued_at?->format('d M Y'),
            ] : [
                'valid' => false,
            ],
            'certificate_number' => $certificateNumber,
        ]);
    }

    /**
     * The typed-lookup form (no certificate_number in the URL yet).
     */
    public function lookup(): Response
    {
        return Inertia::render('Verify/Lookup');
    }
}
