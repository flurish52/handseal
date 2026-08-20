<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class PaystackService
{
    private string $secretKey;

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
    }

    /**
     * Starts a transaction. Returns Paystack's authorization_url to redirect the
     * business owner to, plus the reference we generated (store it as 'pending'
     * before redirecting so the webhook/callback has something to match against).
     */
    public function initialize(string $email, int $amountKobo, ?string $callbackUrl = null): array
    {
        $reference = 'HS-' . Str::upper(Str::random(12));

        $response = Http::withToken($this->secretKey)
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => $email,
                'amount' => $amountKobo,
                'reference' => $reference,
                'callback_url' => $callbackUrl,
            ]);

        if ($response->failed() || ! $response->json('status')) {
            throw new RuntimeException('Paystack initialize failed: ' . $response->body());
        }

        return [
            'reference' => $reference,
            'authorization_url' => $response->json('data.authorization_url'),
        ];
    }

    /**
     * Confirms a transaction actually succeeded — never trust the callback
     * redirect alone, always re-verify server-side against Paystack directly.
     */
    public function verify(string $reference): bool
    {
        $response = Http::withToken($this->secretKey)
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        return $response->successful()
            && $response->json('data.status') === 'success';
    }

    /**
     * Validates the x-paystack-signature header on incoming webhooks —
     * required so a payment can't be faked by hitting the webhook URL directly.
     */
    public function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        if (! $signature) {
            return false;
        }

        $expected = hash_hmac('sha512', $payload, $this->secretKey);

        return hash_equals($expected, $signature);
    }
}
