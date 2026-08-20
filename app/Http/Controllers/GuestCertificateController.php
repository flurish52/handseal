<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuestCertificateRequest;
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
            'programmes' => $business->programmes()->where('is_archived', false)->get(['id', 'name']),
            'builtins' => [
                ['key' => 'classic-navy', 'label' => 'Classic Navy & Brass'],
            ],
            'customTemplates' => $business->certificateTemplates()->where('status', 'active')->get(['id', 'name']),
        ]);
    }

    public function store(StoreGuestCertificateRequest $request, CertificateService $certificateService): \Symfony\Component\HttpFoundation\Response
    {
        $business = Auth::user()->businesses()->firstOrFail();

        $certificate = $certificateService->issueGuest($business, $request->validated());

        return Inertia::location(route('certificates.download', $certificate));
    }
}
