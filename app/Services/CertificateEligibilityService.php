<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Certificate;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class CertificateEligibilityService
{
    /**
     * Checks eligibility and, if paying-as-you-go, atomically debits the wallet.
     * Returns ['allowed' => bool, 'charged' => bool, 'amount_kobo' => int|null, ...]
     */
    public function chargeForIssuance(Business $business): array
    {
        $subscription = $business->activeSubscription();

        if ($subscription) {
            return $this->chargeAgainstSubscription($business, $subscription);
        }

        return $this->chargeAgainstFreeTier($business);
    }

    private function chargeAgainstFreeTier(Business $business): array
    {
        $freePlan = Plan::where('slug', 'free')->firstOrFail();

        return DB::transaction(function () use ($business, $freePlan) {
            $usedThisMonth = Certificate::where('business_id', $business->id)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->lockForUpdate()
                ->count();

            if ($usedThisMonth < $freePlan->included_certs) {
                return ['allowed' => true, 'charged' => false, 'source' => 'free_quota'];
            }

            // Free quota exhausted this month.
            if (is_null($freePlan->extra_cert_price)) {
                return [
                    'allowed' => false,
                    'reason' => 'quota_exhausted_no_overage',
                    'message' => "You've used all {$freePlan->included_certs} free certificates this month. Subscribe to a plan or fund your wallet to keep issuing.",
                ];
            }

            return $this->chargeWallet($business, $freePlan->extra_cert_price, 'cert_issued');
        });
    }

    private function chargeAgainstSubscription(Business $business, Subscription $subscription): array
    {
        $plan = $subscription->plan;

        if ($plan->isUnlimited()) {
            return ['allowed' => true, 'charged' => false, 'source' => 'unlimited'];
        }

        return DB::transaction(function () use ($business, $subscription, $plan) {
            $locked = Subscription::lockForUpdate()->findOrFail($subscription->id);

            if ($locked->certs_used_this_period < $plan->included_certs) {
                $locked->increment('certs_used_this_period');
                return ['allowed' => true, 'charged' => false, 'source' => 'plan_quota'];
            }

            // Quota exhausted for this period
            if (is_null($plan->extra_cert_price)) {
                return [
                    'allowed' => false,
                    'reason' => 'quota_exhausted_no_overage',
                    'message' => "You've used all {$plan->included_certs} certificates included in your {$plan->name} plan this period. Upgrade your plan to issue more.",
                ];
            }

            return $this->chargeWallet($business, $plan->extra_cert_price, 'plan_overage');
        });
    }

    private function chargeWallet(Business $business, int $amountKobo, string $reason): array
    {
        $wallet = $business->wallet()->lockForUpdate()->first();

        if (! $wallet || $wallet->balance < $amountKobo) {
            return [
                'allowed' => false,
                'reason' => 'needs_funds',
                'message' => 'Fund your wallet or subscribe to a plan to keep issuing certificates.',
            ];
        }

        $wallet->decrement('balance', $amountKobo);
        $wallet->refresh();

        $txn = $wallet->transactions()->create([
            'type' => 'debit',
            'amount' => $amountKobo,
            'balance_after' => $wallet->balance,
            'reason' => $reason,
        ]);

        return ['allowed' => true, 'charged' => true, 'amount_kobo' => $amountKobo, 'transaction' => $txn];
    }

    public function refund(Business $business, WalletTransaction $originalTxn, string $reason = 'cert_issue_failed'): void
    {
        DB::transaction(function () use ($business, $originalTxn, $reason) {
            $wallet = $business->wallet()->lockForUpdate()->first();
            $wallet->increment('balance', $originalTxn->amount);
            $wallet->refresh();

            $wallet->transactions()->create([
                'type' => 'credit',
                'amount' => $originalTxn->amount,
                'balance_after' => $wallet->balance,
                'reason' => $reason,
                'meta' => ['reversed_transaction_id' => $originalTxn->id],
            ]);
        });
    }

    public function feeOwed(Business $business): array
    {
        $subscription = $business->activeSubscription();
        $paygPrice = config('handseal.pay_as_you_go_kobo');

        if ($subscription) {
            $plan = $subscription->plan;

            if ($plan->isUnlimited() || $subscription->remainingQuota() > 0) {
                return ['status' => 'covered'];
            }

            // Capped plan, quota exhausted. Use the plan's own overage price
            // if it has one, otherwise fall back to the standard PAYG price
            // rather than blocking.
            $amount = $plan->extra_cert_price ?? $paygPrice;
            return ['status' => 'payable', 'amount_kobo' => $amount];
        }

        $freePlan = Plan::where('slug', 'free')->firstOrFail();
        $usedThisMonth = Certificate::where('business_id', $business->id)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        if ($usedThisMonth < $freePlan->included_certs) {
            return ['status' => 'covered'];
        }

        // No subscription, free quota exhausted — always payable at PAYG price.
        return ['status' => 'payable', 'amount_kobo' => $freePlan->extra_cert_price ?? $paygPrice];
    }

    public function chargeWalletDirectly(Business $business, int $amountKobo, string $reason): array
    {
        return DB::transaction(function () use ($business, $amountKobo, $reason) {
            $wallet = $business->wallet()->lockForUpdate()->first();

            if (! $wallet || $wallet->balance < $amountKobo) {
                return [
                    'allowed' => false,
                    'reason' => 'needs_funds',
                    'message' => 'Fund your wallet or subscribe to a plan to keep issuing certificates.',
                ];
            }

            $wallet->decrement('balance', $amountKobo);
            $wallet->refresh();

            $txn = $wallet->transactions()->create([
                'type' => 'debit',
                'amount' => $amountKobo,
                'balance_after' => $wallet->balance,
                'reason' => $reason,
            ]);

            return ['allowed' => true, 'charged' => true, 'amount_kobo' => $amountKobo, 'transaction' => $txn];
        });
    }
}
