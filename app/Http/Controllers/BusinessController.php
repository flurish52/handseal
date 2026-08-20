<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBusinessRequest;
use App\Http\Requests\UpdateBusinessRequest;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BusinessController extends Controller
{
    /**
     * Onboarding screen — shown once, right after first Google sign-in.
     * Not part of signup itself; Google sign-in already happened by this point.
     */
    public function create(): Response|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->businesses()->exists()) {
            $this->attachReferralIfPresent(null);
            return redirect()->route('dashboard');
        }

        return Inertia::render('Business/Create');
    }
    public function store(StoreBusinessRequest $request): RedirectResponse
    {
        if (Auth::user()?->businesses()->exists()) {
            return redirect()->route('dashboard');
        }

        Auth::user()?->businesses()->create(
            $request->safe()->except('referral_code')
        );


        $this->attachReferralIfPresent($request->validated('referral_code'));

        return redirect()->route('dashboard')->with('success', 'Welcome to HandSeal!');
    }

    /**
     * Typed-in referral code (onboarding form) takes priority over a ?ref= link
     * captured earlier in session. TODO: once Paystack (step 14) tracks payments,
     * change the settings-screen lock below from "already referred" to "already paid".
     */

    private function attachReferralIfPresent(?string $typedCode): void
    {
        $code = $typedCode ?: session('referral_code');
        session()->forget('referral_code');

        if (! $code) {
            return;
        }

        $referrer = User::where('referral_code', $code)->first();

        if (! $referrer || $referrer->id === Auth::id()) {
            return;
        }

        DB::transaction(function () use ($referrer) {
            // Lock this user's row for the duration of the transaction so a
            // concurrent request can't read a stale (pre-update) referred_by
            // value and slip through the same check.
            $user = User::where('id', Auth::id())->lockForUpdate()->first();

            if ($user->referred_by) {
                return;
            }

            $user->update(['referred_by' => $referrer->id]);

            // firstOrCreate is a second safety net — if the unique constraint
            // somehow still gets raced, this throws instead of silently
            // duplicating, and the transaction rolls back cleanly.
            Referral::firstOrCreate(
                ['referred_user_id' => $user->id],
                [
                    'referrer_user_id' => $referrer->id,
                    'reward_percent' => config('handseal.referral_reward_percent'),
                    'status' => 'pending',
                ]
            );
        });
    }

    /**
     * Settings screen — business_name + is_publicly_visible (directory opt-in).
     * trade_category intentionally left off for v1.
     */
    public function edit(): Response
    {
        return Inertia::render('Business/Edit', [
            'business' => Auth::user()->businesses()->firstOrFail(),
            'referralCode' => Auth::user()->referredBy?->referral_code,
            'referralLocked' => (bool) Auth::user()->referred_by,
        ]);
    }

    public function update(UpdateBusinessRequest $request): RedirectResponse
    {
        $business = Auth::user()->businesses()->firstOrFail();

        $business->update($request->safe()->only(['business_name', 'is_publicly_visible']));

        $this->attachReferralIfPresent($request->validated('referral_code'));

        return back()->with('success', 'Settings saved.');
    }
}
