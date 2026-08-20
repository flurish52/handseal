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

        return Inertia::render('Dashboard/Index', [
            'businessName' => $business->business_name,
            'activeStudentsCount' => $business->students()->where('status', 'active')->count(),
            'lifetimeCertificatesCount' => $business->certificates()->count(),
        ]);
    }
}
