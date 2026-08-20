<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use App\Services\CertificateService;
use App\Services\PaystackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function payOnboardingFee(PaystackService $paystack): Response
    {
        return $this->initiatePayment(
            'onboarding',
            config('handseal.onboarding_fee_kobo'),
            $paystack,
            returnTo: url()->previous()
        );
    }

    public function payCertificateFee(Request $request, PaystackService $paystack): Response
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'builtin_template_key' => 'nullable|string',
            'certificate_template_id' => 'nullable|exists:certificate_templates,id',
        ]);

        return $this->initiatePayment(
            'certificate',
            config('handseal.pay_as_you_go_kobo'),
            $paystack,
            metadata: $validated,
            returnTo: route('students.index')
        );
    }

    public function paySubscription(PaystackService $paystack): Response
    {
        return $this->initiatePayment(
            'subscription',
            config('handseal.subscription_monthly_kobo'),
            $paystack,
            returnTo: route('students.index')
        );
    }

    private function initiatePayment(string $type, int $amountKobo, PaystackService $paystack, ?array $metadata = null, ?string $returnTo = null): Response
    {
        $business = Auth::user()->businesses()->firstOrFail();

        $init = $paystack->initialize(Auth::user()->email, $amountKobo, route('payments.callback'));

        $business->payments()->updateOrCreate(
            ['type' => $type, 'status' => 'pending'],
            [
                'amount_kobo' => $amountKobo,
                'paystack_reference' => $init['reference'],
                'metadata' => $metadata,
                'return_to' => $returnTo ?? route('dashboard'),
            ]
        );

        return Inertia::location($init['authorization_url']);
    }

    /**
     * Browser lands here after paying — re-verify server-side, never trust the redirect alone.
     */
    public function callback(Request $request, PaystackService $paystack): RedirectResponse
    {
        $reference = $request->query('reference');
        $payment = Payment::where('paystack_reference', $reference)->firstOrFail();

        if ($paystack->verify($reference)) {
            $this->markPaid($payment);
            $payment->refresh();

            return redirect($payment->return_to ?? route('dashboard'))->with(array_filter([
                'success' => 'Payment successful.',
                'download_url' => $payment->certificate_id
                    ? route('certificates.download', $payment->certificate_id)
                    : null,
            ]));
        }

        return redirect($payment->return_to ?? route('dashboard'))->with('error', 'Payment could not be confirmed.');
    }

    /**
     * Source of truth for marking a payment successful — webhooks fire even if the
     * business owner closes the tab before the callback redirect completes.
     */
    public function webhook(Request $request, PaystackService $paystack): \Illuminate\Http\Response
    {
        $signature = $request->header('x-paystack-signature');

        if (! $paystack->verifyWebhookSignature($request->getContent(), $signature)) {
            abort(400, 'Invalid signature');
        }

        $event = $request->input('event');
        $reference = $request->input('data.reference');

        if ($event === 'charge.success' && $reference) {
            $payment = Payment::where('paystack_reference', $reference)->first();

            if ($payment && $payment->status !== 'successful') {
                $this->markPaid($payment);
            }
        }

        return response('', 200);
    }

    private function markPaid(Payment $payment): void
    {
        $payment->update(['status' => 'successful', 'paid_at' => now()]);

        if ($payment->type === 'subscription') {
            $business = $payment->business;
            $extendFrom = $business->subscription_active_until && $business->subscription_active_until->isFuture()
                ? $business->subscription_active_until
                : now();
            $business->update(['subscription_active_until' => $extendFrom->copy()->addMonth()]);
        }

        if ($payment->type === 'certificate' && $payment->metadata && ! $payment->certificate_id) {
            $student = Student::find($payment->metadata['student_id'] ?? null);

            if ($student) {
                $certificate = app(CertificateService::class)->issueForStudent($payment->business, $student, [
                    'builtin_template_key' => $payment->metadata['builtin_template_key'] ?? null,
                    'certificate_template_id' => $payment->metadata['certificate_template_id'] ?? null,
                ]);

                $payment->update(['certificate_id' => $certificate->id]);
            }
        }
    }
}
