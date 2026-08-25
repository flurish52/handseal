<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Student;
use App\Models\Subscription;
use App\Services\CertificateEligibilityService;
use App\Services\CertificateService;
use App\Services\PaystackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    /**
     * CHANGED. This used to also handle wallet-funded purchases via a
     * `source` field, defaulting to 'gateway' only when the field was
     * absent — but the frontend was explicitly sending `source: wallet`
     * on the plain pay-as-you-go flow, silently debiting the wallet
     * instead of charging a card. That's the bug you hit.
     *
     * Fixed by removing the branch entirely: this endpoint now ONLY ever
     * charges via Paystack (card/bank), full stop, no wallet check, no
     * `source` field to get wrong. Point your pay-as-you-go "buy this
     * certificate" button at this route and nothing else.
     *
     * Paying from an existing wallet balance is now a deliberately
     * separate action — see payCertificateFeeFromWalletBalance() below —
     * reachable only from wherever you explicitly offer "pay from wallet"
     * (e.g. the billing page, once the business already has a balance).
     */
    public function payCertificateFee(Request $request, PaystackService $paystack, CertificateEligibilityService $eligibility): Response
    {
        $validated = $this->validateCertificatePurchase($request);

        $business = $request->user()->businesses()->firstOrFail();
        $fee = $eligibility->feeOwed($business);

        if ($fee['status'] === 'covered') {
            return back()->with('error', 'No payment is currently required, you already have quota available.');
        }

        $returnTo = $validated['student_id'] ? route('students.index') : route('certificates.guest.create');

        return $this->initiatePayment('certificate', $fee['amount_kobo'], $paystack, metadata: $validated, returnTo: $returnTo);
    }

    /**
     * NEW. The wallet-balance path, split out of payCertificateFee() —
     * only reachable if something explicitly posts to this route, never
     * as a side effect of a stray form field.
     */
    public function payCertificateFeeFromWalletBalance(Request $request, CertificateEligibilityService $eligibility): RedirectResponse
    {
        $validated = $this->validateCertificatePurchase($request);

        $business = $request->user()->businesses()->firstOrFail();
        $fee = $eligibility->feeOwed($business);

        if ($fee['status'] === 'covered') {
            return back()->with('error', 'No payment is currently required, you already have quota available.');
        }

        $returnTo = $validated['student_id'] ? route('students.index') : route('certificates.guest.create');

        return $this->payCertificateFeeFromWallet($business, $eligibility, $fee['amount_kobo'], $validated, $returnTo);
    }

    /**
     * NEW. CertificateService::issueForStudent() only wants the template
     * selection (certificate_template_id OR builtin_template_key) — every
     * other field (name, dates, programme) comes from the Student model
     * itself. But every call site here was passing the FULL validated
     * payload as that argument, and issueForStudent() spreads it straight
     * into the create() array AFTER setting the correct student-derived
     * fields — so programme_id, recipient_name, start_date, and end_date
     * were all being silently overwritten with null (the student form
     * never sends those keys), and programme_id's NOT NULL constraint is
     * what surfaced it as a crash. This narrows the payload to only what
     * issueForStudent() actually expects.
     */
    private function templateSelectionOnly(array $data): array
    {
        return Arr::only($data, ['certificate_template_id', 'builtin_template_key']);
    }

    private function validateCertificatePurchase(Request $request): array
    {
        $validated = $request->validate([
            'student_id' => 'nullable|exists:students,id',
            'recipient_name' => 'nullable|string|max:255',
            'programme_id' => 'nullable|exists:programmes,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'builtin_template_key' => 'nullable|string',
            'certificate_template_id' => 'nullable|exists:certificate_templates,id',
        ]);

        if (! $validated['student_id'] && ! $validated['recipient_name']) {
            abort(422, 'Either student_id or recipient_name is required.');
        }

        return $validated;
    }

    /**
     * CHANGED. Was: charge wallet, issue certificate, then create the
     * Payment row as three separate un-transacted steps. If the third step
     * failed (as it just did — NOT NULL constraint on paystack_reference),
     * the certificate was already committed with no matching payment row:
     * an orphaned record that's silently wrong rather than loudly broken.
     *
     * Now: certificate issuance + payment record are one DB transaction —
     * either both exist or neither does. The wallet charge stays a separate
     * step before the transaction (chargeWalletDirectly() already commits
     * its own debit), so on failure we still explicitly refund it via
     * $eligibility->refund(), same as before — that part of the original
     * logic was correct, just not paired with a transaction around the rest.
     *
     * NOTE: CertificateService::issueForStudent()/issueGuest() likely write
     * a file to storage in addition to the DB row. A DB rollback here won't
     * delete that file — a failure leaves an orphaned file on disk, not an
     * orphaned DB row. Lesser problem than before, not zero; worth a
     * cleanup job later if this path fails often.
     */
    private function payCertificateFeeFromWallet(Business $business, CertificateEligibilityService $eligibility, int $feeKobo, array $validated, string $returnTo): RedirectResponse
    {
        $charge = $eligibility->chargeWalletDirectly($business, $feeKobo, 'cert_issued');

        if (! $charge['allowed']) {
            return redirect()->to($returnTo)->with(['error' => $charge['message'], 'paywall' => $charge['reason']]);
        }

        try {
            $payment = DB::transaction(function () use ($business, $validated, $feeKobo, $returnTo) {
                $certificate = $validated['student_id']
                    ? app(CertificateService::class)->issueForStudent($business, Student::findOrFail($validated['student_id']), $this->templateSelectionOnly($validated))
                    : app(CertificateService::class)->issueGuest($business, $validated);

                return $this->recordSuccessfulPayment($business, 'certificate', $feeKobo, $validated, $returnTo, $certificate->id);
            });
        } catch (\Throwable $e) {
            $eligibility->refund($business, $charge['transaction'], 'cert_issue_failed');
            report($e);

            return redirect()->to($returnTo)->with('error', 'Payment succeeded but the certificate could not be issued. Your wallet has been refunded.');
        }

        return redirect()->to($returnTo)->with([
            'success' => 'Certificate issued.',
            'download_url' => route('certificates.download', $payment->certificate_id),
        ]);
    }

    /**
     * NEW. Extracted so the shape of a "successful, already-paid" payment
     * record (used here, and previously duplicated inline) lives in one
     * place. paystack_reference is nullable now (see accompanying
     * migration) — non-gateway payments like this one legitimately don't
     * have one.
     */
    private function recordSuccessfulPayment(Business $business, string $type, int $amountKobo, array $metadata, string $returnTo, ?int $certificateId = null): Payment
    {
        return $business->payments()->create([
            'type' => $type,
            'status' => 'successful',
            'amount_kobo' => $amountKobo,
            'paystack_reference' => null,
            'metadata' => $metadata,
            'return_to' => $returnTo,
            'certificate_id' => $certificateId,
            'paid_at' => now(),
        ]);
    }

    public function payCustomCertFee(PaystackService $paystack): Response
    {
        return $this->initiatePayment(
            'template_fee',
            config('handseal.custom_cert_fee_kobo'),
            $paystack,
            metadata: ['purpose' => 'new_cycle'],
            returnTo: route('certificate-templates.index')
        );
    }

    public function payTemplateRequestFee(PaystackService $paystack): Response
    {
        return $this->initiatePayment(
            'template_fee',
            config('handseal.custom_cert_fee_kobo'),
            $paystack,
            metadata: ['purpose' => 'team_request'],
            returnTo: route('certificate-templates.index')
        );
    }

    public function paySubscription(Plan $plan, PaystackService $paystack): Response
    {
        abort_unless($plan->is_active, 404);

        return $this->initiatePayment(
            'subscription',
            $plan->price,
            $paystack,
            metadata: ['plan_id' => $plan->id],
            returnTo: route('billing.index')
        );
    }

    public function payFundWallet(Request $request, PaystackService $paystack): Response
    {
        $validated = $request->validate([
            'amount_naira' => 'required|integer|min:100',
        ]);

        $amountKobo = $validated['amount_naira'] * 100;

        return $this->initiatePayment(
            'funding',
            $amountKobo,
            $paystack,
            metadata: ['purpose' => 'wallet_topup'],
            returnTo: route('billing.index')
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
            $this->activateSubscription($payment);
        }

        if ($payment->type === 'funding') {
            $this->creditWallet($payment);
        }

        if ($payment->type === 'certificate' && $payment->metadata && ! $payment->certificate_id) {
            $studentId = $payment->metadata['student_id'] ?? null;

            try {
                $certificate = $studentId
                    ? app(CertificateService::class)->issueForStudent(
                        $payment->business,
                        Student::findOrFail($studentId),
                        $this->templateSelectionOnly($payment->metadata)
                    )
                    : app(CertificateService::class)->issueGuest($payment->business, $payment->metadata);

                $payment->update(['certificate_id' => $certificate->id]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        if ($payment->type === 'template_fee' && ($payment->metadata['purpose'] ?? null) === 'new_cycle') {
            $payment->business->update(['ai_attempts_remaining' => 3]);
        }

        if ($payment->type === 'template_fee' && ($payment->metadata['purpose'] ?? null) === 'team_request') {
            $requestId = $payment->metadata['certificate_template_request_id'] ?? null;

            if ($requestId) {
                \App\Models\CertificateTemplateRequest::where('id', $requestId)->update(['status' => 'pending']);
            }
        }
    }

    private function activateSubscription(Payment $payment): void
    {
        $plan = Plan::find($payment->metadata['plan_id'] ?? null);

        if (! $plan) {
            return;
        }

        $business = $payment->business;
        $existing = $business->activeSubscription();

        $startFrom = ($existing && $existing->current_period_ends_at->isFuture())
            ? $existing->current_period_ends_at
            : now();

        if ($existing) {
            $existing->update([
                'plan_id' => $plan->id,
                'status' => 'active',
                'current_period_ends_at' => $startFrom->copy()->addMonth(),
            ]);
        } else {
            Subscription::create([
                'business_id' => $business->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'started_at' => now(),
                'current_period_ends_at' => $startFrom->copy()->addMonth(),
            ]);
        }
    }

    private function creditWallet(Payment $payment): void
    {
        $wallet = $payment->business->wallet ?? $payment->business->wallet()->create(['balance' => 0]);

        $wallet->credit(
            $payment->amount_kobo,
            'wallet_topup',
            ['payment_id' => $payment->id]
        );
    }
}
