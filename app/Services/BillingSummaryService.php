<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Certificate;
use App\Models\Plan;

class BillingSummaryService
{
    public function for(Business $business): array
    {
        $wallet = $business->wallet;
        $subscription = $business->activeSubscription();

        if ($subscription) {
            $plan = $subscription->plan;
            $sub = [
                'plan_slug' => $plan->slug,
                'plan_name' => $plan->name,
                'is_unlimited' => $plan->isUnlimited(),
                'is_free' => false,
                'included_certs' => $plan->included_certs,
                'certs_used' => $subscription->certs_used_this_period,
                'remaining' => $subscription->remainingQuota(),
                'current_period_ends_at' => $subscription->current_period_ends_at->toIso8601String(),
                'extra_cert_price_kobo' => $plan->extra_cert_price,
            ];
        } else {
            $freePlan = Plan::where('slug', 'free')->first();
            $monthStart = now()->startOfMonth();
            $monthEnd = now()->endOfMonth();

            $usedThisMonth = Certificate::where('business_id', $business->id)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();

            $sub = [
                'plan_slug' => $freePlan->slug,
                'plan_name' => $freePlan->name,
                'is_unlimited' => false,
                'is_free' => true,
                'included_certs' => $freePlan->included_certs,
                'certs_used' => $usedThisMonth,
                'remaining' => max(0, $freePlan->included_certs - $usedThisMonth),
                'current_period_ends_at' => $monthEnd->toIso8601String(),
                'extra_cert_price_kobo' => $freePlan->extra_cert_price,
            ];
        }

        return [
            'wallet_balance_kobo' => $wallet->balance ?? 0,
            'payg_price_kobo' => config('handseal.pay_as_you_go_kobo'),
            'subscription' => $sub,
        ];
    }
}
