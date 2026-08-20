<?php

namespace App\Services;

use App\Models\Business;

class CertificateEligibilityService
{
    public function check(Business $business): array
    {
        if ($business->hasActiveSubscription()) {
            return ['allowed' => true];
        }

        $onboardingPayment = $business->onboardingPayment();

        if (! $onboardingPayment) {
            $issuedCount = $business->certificates()->count();

            if ($issuedCount >= config('handseal.free_certificates_before_onboarding')) {
                return [
                    'allowed' => false,
                    'reason' => 'needs_onboarding',
                    'message' => 'You\'ve used your free preview certificate. Pay the one-time onboarding fee to continue.',
                ];
            }

            return ['allowed' => true];
        }

        $postOnboardingCount = $business->certificates()
            ->where('issued_at', '>=', $onboardingPayment->paid_at)
            ->count();

        if ($postOnboardingCount >= config('handseal.free_certificates_after_onboarding')) {
            return [
                'allowed' => false,
                'reason' => 'needs_payment',
                'message' => 'You\'ve used your free certificates. Pay per certificate or subscribe for unlimited issuing.',
            ];
        }

        return ['allowed' => true];
    }
}
