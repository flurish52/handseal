<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ReferralController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        $referrerEligible = $user->businesses()
            ->whereHas('payments', fn ($q) => $q
                ->where('type', 'onboarding')
                ->where('status', 'successful'))
            ->exists();

        return Inertia::render('Referrals/Index', [
            'referralCode' => $user->referral_code,
            'referralLink' => rtrim(config('app.url'), '/') . '/onboarding?ref=' . $user->referral_code,
            'referrerEligible' => $referrerEligible,
            'referrals' => $user->referralsMade()->with('referred:id,name')->latest()->get()
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'referred_name' => $r->referred->name,
                    'status' => $r->status,
                    'reward_percent' => $r->reward_percent,
                    'eligible' => $r->status === 'pending'
                        && $r->referred->businesses()->whereHas('payments', fn ($q) => $q
                            ->where('type', 'onboarding')
                            ->where('status', 'successful'))->exists(),
                ]),
        ]);
    }

    public function requestPayout(Referral $referral): RedirectResponse
    {
        if ($referral->referrer_user_id !== Auth::id()) {
            abort(403);
        }

        if ($referral->status !== 'pending') {
            return back()->with('error', 'This referral has already been submitted for payout.');
        }

        $referrerEligible = Auth::user()
            ->businesses()
            ->whereHas('payments', fn ($q) => $q
                ->where('type', 'onboarding')
                ->where('status', 'successful'))
            ->exists();

        if (! $referrerEligible) {
            return back()->with(
                'error',
                'You need to complete your own onboarding payment before you can request referral payouts.'
            );
        }

        $hasPaid = $referral->referred
            ->businesses()
            ->whereHas('payments', fn ($q) => $q
                ->where('type', 'onboarding')
                ->where('status', 'successful'))
            ->exists();

        if (! $hasPaid) {
            return back()->with(
                'error',
                'This one isn\'t ready yet — your bonus unlocks once the business completes their first payment. We\'ll let you know the moment it does.'
            );
        }

        $referral->update(['status' => 'requested']);

        return back()->with(
            'success',
            'Payout requested! We review and process referral bonuses manually — expect to hear from us shortly.'
        );
    }
}
