<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $business = Auth::user()->businesses()->firstOrFail();

        $certificates = $business->certificates();

        return Inertia::render('Dashboard/Index', [
            'businessName' => $business->business_name,
            'activeStudentsCount' => $business->students()->where('status', 'active')->count(),
            'lifetimeCertificatesCount' => (clone $certificates)->count(),
            'certificatesThisMonth' => (clone $certificates)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            'recentCertificates' => (clone $certificates)
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'recipient_name' => $c->recipient_name,
                    'certificate_number' => $c->certificate_number,
                    'download_url' => route('certificates.download', $c->id),
                    'created_at' => $c->created_at->toIso8601String(),
                ]),

        ]);
    }
}
