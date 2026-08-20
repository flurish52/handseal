<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException $e) {
            return redirect()->route('login')
                ->with('error', 'Google sign-in expired or was invalid. Please try again.');
        } catch (\Throwable $e) {
            Log::error('Google OAuth callback failed', ['message' => $e->getMessage()]);
            return redirect()->route('login')
                ->with('error', 'Something went wrong signing in with Google.');
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'New User',
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => bcrypt(Str::random(32)), // unused, but satisfies column if not nullable
                'email_verified_at' => now(),
            ]);

            $user->generateReferralCode();
        } elseif (! $user->google_id) {
            $user->google_id = $googleUser->getId();
            $user->save();
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        if (! $user->businesses()->exists()) {
            return redirect()->route('business.create');
        }

        return redirect()->route('dashboard');
    }
}
