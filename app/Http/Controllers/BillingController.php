<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\BillingSummaryService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function index(BillingSummaryService $billing): Response
    {
        $business = Auth::user()->businesses()->firstOrFail();
        $summary = $billing->for($business);
        $wallet = $business->wallet;

        return Inertia::render('Billing/Index', [
            'subscription' => $summary['subscription'],
            'wallet' => [
                'balance_kobo' => $summary['wallet_balance_kobo'],
                'payg_price_kobo' => $summary['payg_price_kobo'],
            ],
            'plans' => Plan::active()->get(),
            'transactions' => $wallet?->transactions()->latest()->limit(20)->get() ?? [],
        ]);
    }
}
