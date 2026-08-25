<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Models\Plan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RenewSubscriptions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Subscription::where('status', 'active')
            ->where('current_period_ends_at', '<=', now())
            ->with('plan', 'business.wallet')
            ->chunkById(100, function ($subscriptions) {
                foreach ($subscriptions as $subscription) {
                    $this->renewOrExpire($subscription);
                }
            });
    }

    private function renewOrExpire(Subscription $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            // Lock the subscription row itself to avoid double-processing
            // if the job overlaps with a manual renewal/cancellation.
            $locked = Subscription::lockForUpdate()->find($subscription->id);

            if (! $locked || $locked->status !== 'active' || $locked->current_period_ends_at->isFuture()) {
                return;
            }

            $plan = $locked->plan;
            $business = $locked->business;
            $wallet = $business->wallet()->lockForUpdate()->first();

            // Free plan has no price — nothing to charge, just roll the
            // period forward and reset usage.
            if ($plan->price <= 0) {
                $locked->update([
                    'current_period_ends_at' => $locked->current_period_ends_at->addMonth(),
                    'certs_used_this_period' => 0,
                ]);
                return;
            }

            if (! $wallet || $wallet->balance < $plan->price) {
                $locked->update(['status' => 'expired']);
                Log::info('Subscription expired — insufficient wallet funds', [
                    'subscription_id' => $locked->id,
                    'business_id' => $business->id,
                    'required_kobo' => $plan->price,
                    'wallet_balance_kobo' => $wallet->balance ?? 0,
                ]);
                return;
            }

            $wallet->decrement('balance', $plan->price);
            $wallet->refresh();

            $wallet->transactions()->create([
                'type' => 'debit',
                'amount' => $plan->price,
                'balance_after' => $wallet->balance,
                'reason' => 'subscription_renewal',
                'meta' => ['subscription_id' => $locked->id, 'plan_id' => $plan->id],
            ]);

            $locked->update([
                'current_period_ends_at' => $locked->current_period_ends_at->addMonth(),
                'certs_used_this_period' => 0,
            ]);

            Log::info('Subscription renewed from wallet', [
                'subscription_id' => $locked->id,
                'business_id' => $business->id,
                'charged_kobo' => $plan->price,
            ]);
        });
    }
}
