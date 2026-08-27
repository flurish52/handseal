<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VerificationController extends Controller
{
    /**
     * The typed-lookup form (no certificate_number in the URL yet).
     */
    public function lookup(): Response
    {
        return Inertia::render('Verify/Lookup');
    }

    /**
     * Handles the typed-in search. Never redirects with a raw, unsanitized
     * user input into a URL segment — a slash in what they typed would break
     * routing. On a match, redirect to the clean, bookmarkable /verify/{slug}
     * URL. On no match, render the same result page directly (no redirect)
     * so the "not found" state is guaranteed visible regardless of whether
     * this layout has a flash/toast system.
     */
    public function search(Request $request): Response|RedirectResponse
    {
        $request->validate([
            'certificate_number' => 'required|string|max:64',
        ]);

        $certificate = Certificate::where('certificate_number', $request->certificate_number)
            ->orWhere('public_verification_number', $request->certificate_number)
            ->first();

        if (! $certificate) {
            return Inertia::render('Verify/Show', [
                'result' => ['valid' => false],
                'certificate_number' => $request->certificate_number,
            ]);
        }

        $slug = $certificate->public_verification_number ?? $certificate->certificate_number;

        return redirect()->route('verify.show', $slug);
    }

    /**
     * Public — no auth. Lookup by public_verification_number (new certs) or
     * certificate_number (old certs, both typed-in and QR-scanned land here).
     * Never expose anything beyond these 5 fields — no student phone, no
     * programme price, no business owner info, nothing else on the row.
     */
    public function show(string $code): Response
    {
        $certificate = Certificate::where('public_verification_number', $code)
            ->orWhere('certificate_number', $code)
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
            'certificate_number' => $code,
        ]);
    }
}
