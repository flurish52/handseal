<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{

public function index(): Response
{
    $plans = Plan::active()->get();

    $subscription = null;
    $walletBalance = 0;

    if (Auth::check()) {
        $business = Auth::user()->businesses()->first();

        if ($business) {
            $subscription = $business->activeSubscription();

            $wallet = $business->wallet ?? $business->wallet()->create([
                'balance' => 0,
            ]);

            $walletBalance = $wallet->balance;
        }
    }

    return Inertia::render('Plans/Index', [
        'plans' => $plans,

        'subscription' => $subscription ? [
            'plan_id' => $subscription->plan_id,
            'plan_name' => $subscription->plan->name,
            'status' => $subscription->status,
            'started_at' => $subscription->started_at?->format('M j, Y'),
            'current_period_ends_at' => $subscription->current_period_ends_at?->format('M j, Y'),
            'is_active' => $subscription->isActive(),
        ] : null,

        'wallet_balance_kobo' => $walletBalance,
        'wallet_balance_naira' => '₦' . number_format($walletBalance / 100, 0),
    ]);
}
}
