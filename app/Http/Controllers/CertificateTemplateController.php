<?php
// app/Http/Controllers/CertificateTemplateController.php

namespace App\Http\Controllers;

use App\Models\CertificateTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

use App\Http\Requests\GenerateCertificateTemplateRequest;
use App\Models\Business;
use App\Services\GeminiCertificateTemplateService;
use App\Services\InvalidCertificateReferenceException;

class CertificateTemplateController extends Controller
{
    public function index(): Response
    {
        $business = Auth::user()->businesses()->firstOrFail();

        $sampleProgrammeId = method_exists($business, 'programmes')
            ? $business->programmes()->value('id')
            : null;

        $buildPreviewUrl = fn (array $templateParam) => route('certificates.guest.preview', array_merge([
            'recipient_name' => 'Jane Doe',
            'programme_id' => $sampleProgrammeId,
            'start_date' => now()->subMonths(3)->toDateString(),
            'end_date' => now()->toDateString(),
        ], $templateParam));

        $templates = $business->certificateTemplates()
            ->latest()
            ->get(['id', 'name', 'status', 'source', 'created_at'])
            ->map(fn (CertificateTemplate $template) => [
                'id' => $template->id,
                'name' => $template->name,
                'status' => $template->status,
                'source' => $template->source,
                'preview_url' => $buildPreviewUrl(['certificate_template_id' => $template->id]),
            ]);

        $requests = $business->certificateTemplateRequests()
            ->whereIn('status', ['pending', 'in_review', 'declined'])
            ->latest()
            ->get(['id', 'name', 'description', 'status', 'admin_note', 'created_at']);

        $builtins = collect([
            ['key' => 'classic-navy', 'label' => 'Classic Navy & Brass'],
            ['key' => 'executive-sidebar', 'label' => 'Executive Sidebar'],
            ['key' => 'portrait-minimal', 'label' => 'Minimal Modern'],
            ['key' => 'ornate-gold', 'label' => 'Ornate Gold Seal'],
            ['key' => 'portrait-formal', 'label' => 'Formal Report'],
        ])->map(fn (array $builtin) => array_merge($builtin, [
            'preview_url' => $buildPreviewUrl(['builtin_template_key' => $builtin['key']]),
        ]));

        $rejectionCount = $business->ai_rejection_count;

        return Inertia::render('CertificateTemplates/Index', [
            'templates' => $templates,
            'requests' => $requests,
            'builtins' => $builtins,
            'defaultBuiltinKey' => $business->hasActiveCustomTemplate() ? null : $business->default_builtin_template_key,
            'quota' => [
                'attempts_remaining' => $business->ai_attempts_remaining,
                'maxed_out' => $business->ai_attempts_remaining <= 0,
                'has_active_template' => $templates->contains('status', 'active'),
                'has_draft_template' => $templates->contains('status', 'draft'),
                'can_generate_free' => $business->canGenerateAiForFree(),
                'can_request_from_admins' => $business->canRequestFromAdmins(),
                'fee_naira' => config('handseal.custom_cert_fee_kobo') / 100,
            ],
        ]);
    }

    public function activate(CertificateTemplate $certificateTemplate): RedirectResponse
    {
        $this->authorizeOwnership($certificateTemplate);

        // Enforce "one active template" — without this, activating a second
        // template left both active, and resolvedTemplateSelection() would
        // pick whichever the query happened to return first.
        $certificateTemplate->business->certificateTemplates()
            ->where('id', '!=', $certificateTemplate->id)
            ->where('status', 'active')
            ->update(['status' => 'inactive']);

        $certificateTemplate->update(['status' => 'active']);

        return back()->with('success', 'This is now used for every certificate you issue.');
    }

    public function generate(GenerateCertificateTemplateRequest $request, GeminiCertificateTemplateService $aiService): RedirectResponse
    {
        $business = Auth::user()->businesses()->firstOrFail();

        if (! $business->canGenerateAiForFree()) {
            return back()->with('error', 'Pay the custom certificate fee above to continue — either to start a new design or add another certificate.');
        }

        try {
            $content = $aiService->generate(
                $business->business_name,
                $request->validated('description'),
                $request->file('images', []),
                $request->validated('sample_type', 'template')
            );
        } catch (InvalidCertificateReferenceException $e) {
            $business->decrement('ai_attempts_remaining');
            return back()->with('error', $e->getMessage());
        } catch (\RuntimeException $e) {
            report($e);
            return back()->with('error', 'Something went wrong generating your certificate. Please try again shortly.');
        }

        $business->decrement('ai_attempts_remaining');

        $business->certificateTemplates()->create([
            'name' => $request->validated('name'),
            'content' => $content,
            'status' => 'draft',
            'source' => 'ai',
        ]);

        return back()->with('success', 'Draft ready, review it below.');
    }

    public function reject(CertificateTemplate $certificateTemplate): RedirectResponse
    {
        $this->authorizeOwnership($certificateTemplate);
        abort_if($certificateTemplate->status !== 'draft', 422, 'Only pending drafts can be rejected.');

        $certificateTemplate->update(['status' => 'rejected']);

        return back()->with('success', 'Marked as rejected. You can still accept it later, or generate again if you have attempts left.');
    }
    private function checkGenerationQuota(Business $business): array
    {
        if ($business->certificateTemplates()->where('status', 'active')->exists()) {
            return ['allowed' => false, 'message' => "You've already got a custom certificate. Reach out to us to add another."];
        }

        if ($business->ai_rejection_count >= 3) {
            $hasUnreviewedDraft = $business->certificateTemplates()->where('status', 'draft')->exists();

            return [
                'allowed' => false,
                'message' => $hasUnreviewedDraft
                    ? "You've used all 3 attempts. Accept one of your drafts below, or request a template from our team."
                    : "You've used all 3 attempts. Request a template from our team to keep going.",
            ];
        }

        return ['allowed' => true];
    }

    public function destroy(CertificateTemplate $certificateTemplate): RedirectResponse
    {
        $this->authorizeOwnership($certificateTemplate);

        abort_if(
            $certificateTemplate->certificates()->exists(),
            422,
            'This template has certificates issued with it and can\'t be deleted.'
        );

        $certificateTemplate->delete();

        return back()->with('success', 'Template removed.');
    }

    private function authorizeOwnership(CertificateTemplate $certificateTemplate): void
    {
        abort_unless(
            $certificateTemplate->business_id === Auth::user()->businesses()->firstOrFail()->id,
            403
        );
    }

    public function setDefaultBuiltin(\Illuminate\Http\Request $request): RedirectResponse
    {
        $validated = $request->validate(['builtin_template_key' => 'required|string']);
        $business = Auth::user()->businesses()->firstOrFail();

        // Choosing a builtin implicitly steps down whichever custom template
        // was active — same one-active-template rule as activate() above,
        // just approached from the other direction.
        $business->certificateTemplates()->where('status', 'active')->update(['status' => 'inactive']);

        $business->update(['default_builtin_template_key' => $validated['builtin_template_key']]);

        return back()->with('success', 'Default template updated.');
    }
}
