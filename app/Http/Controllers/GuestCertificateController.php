<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuestCertificateRequest;
use App\Services\CertificateEligibilityService;
use App\Services\CertificateService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class GuestCertificateController extends Controller
{
    public function create(): Response
    {
        $business = Auth::user()->businesses()?->firstOrFail();

        return Inertia::render('Certificates/Guest', [
            'programmes' => $business->programmes()->where('is_archived', false)->get(['id', 'name', 'typical_duration']),
            'guestCertificates' => $business->certificates()
                ->where('is_guest', true)
                ->with('programme:id,name')
                ->latest('issued_at')
                ->get(['id', 'recipient_name', 'certificate_number', 'programme_id', 'issued_at']),
        ]);
    }
    public function store(StoreGuestCertificateRequest $request, CertificateService $certificateService, CertificateEligibilityService $eligibility)
    {
        $business = Auth::user()->businesses()->firstOrFail();

        $check = $eligibility->chargeForIssuance($business);

        if (! $check['allowed']) {
            return back()->with(['error' => $check['message'], 'paywall' => $check['reason']]);
        }

        try {
            $certificate = $certificateService->issueGuest($business, array_merge($request->validated(), $business->resolvedTemplateSelection()));
        } catch (\Throwable $e) {
            if ($check['charged']) {
                $eligibility->refund($business, $check['amount_kobo'], 'certificate_issue_failed');
            }
            throw $e;
        }

        return Inertia::location(route('certificates.download', $certificate));
    }
}
