<?php
namespace App\Http\Controllers;

use App\Jobs\GenerateTemplateThumbnail;
use App\Models\CertificateTemplate;
use App\Services\CertificateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

        // Covers builtins AND any future admin-authored premium marketplace
        // templates — both share business_id = null, status = 'active'.
        // No separate $builtins query anymore; builtins are just the
        // non-premium rows in this same set.
        $marketplace = CertificateTemplate::query()
            ->whereNull('business_id')
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get(['id', 'name', 'is_premium', 'price', 'thumbnail_path'])
            ->map(fn (CertificateTemplate $template) => [
                'id' => $template->id,
                'name' => $template->name,
                'is_premium' => $template->is_premium,
                'price' => $template->price,
                'price_naira' => $template->price / 100,
                'thumbnail_url' => $template->thumbnail_path
                    ? Storage::disk('public')->url($template->thumbnail_path)
                    : null,
                // Live render for the preview modal — cheap (plain Blade),
                // unlike thumbnail_path which is a paid htmlcsstoimage call
                // and stays reserved for the grid card itself.
                'preview_url' => $buildPreviewUrl(['certificate_template_id' => $template->id]),
                'owned' => ! $template->is_premium || $template->purchases()->where('business_id', $business->id)->exists(),
            ]);

        return Inertia::render('CertificateTemplates/Index', [
            'templates' => $templates,
            'requests' => $requests,
            'activeCertificateTemplateId' => $business->active_certificate_template_id,
            'quota' => [
                'attempts_remaining' => $business->ai_attempts_remaining,
                'maxed_out' => $business->ai_attempts_remaining <= 0,
                'has_active_template' => $templates->contains('status', 'active'),
                'has_draft_template' => $templates->contains('status', 'draft'),
                'can_generate_free' => $business->canGenerateAiForFree(),
                'can_request_from_admins' => $business->canRequestFromAdmins(),
                'fee_naira' => config('handseal.custom_cert_fee_kobo') / 100,
            ],
            'marketplace' => $marketplace,
            'walletBalanceKobo' => $business->wallet->balance ?? 0,
        ]);
    }

    public function activate(CertificateTemplate $certificateTemplate): RedirectResponse
    {
        $this->authorizeOwnership($certificateTemplate);

        $business = Auth::user()->businesses()->firstOrFail();


        $business->certificateTemplates()
            ->where('id', '!=', $certificateTemplate->id)
            ->where('status', 'active')
            ->update(['status' => 'inactive']);

        $certificateTemplate->update(['status' => 'active']);
        $business->active_certificate_template_id = $certificateTemplate->id;
        $business->save();
        GenerateTemplateThumbnail::dispatch($certificateTemplate);

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

        if ($certificateTemplate->thumbnail_path) {
            Storage::disk('public')->delete($certificateTemplate->thumbnail_path);
        }

        $certificateTemplate->update([
            'status' => 'rejected',
            'thumbnail_path' => null,
        ]);

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
        abort_if($certificateTemplate->business_id === null, 403,
            'Marketplace templates can\'t be deleted.');

        $this->authorizeOwnership($certificateTemplate);

        abort_if(
            $certificateTemplate->certificates()->exists(),
            422,
            'This template has certificates issued with it and can\'t be deleted.'
        );

        $business = Auth::user()->businesses()->firstOrFail();
        $wasActive = $business->active_certificate_template_id === $certificateTemplate->id;

        $certificateTemplate->delete();

        if ($wasActive) {
            $business->update(['active_certificate_template_id' => CertificateTemplate::defaultBuiltin()->id]);
        }

        return back()->with('success', 'Template removed.');
    }

    private function authorizeOwnership(CertificateTemplate $certificateTemplate): void
    {
        $business = Auth::user()->businesses()->firstOrFail();

        $owned = $certificateTemplate->business_id === $business->id
            || (
                $certificateTemplate->business_id === null
                && (
                    ! $certificateTemplate->is_premium
                    || $certificateTemplate->purchases()->where('business_id', $business->id)->exists()
                )
            );

        abort_unless($owned, 403);
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

    public function previewDraft(CertificateTemplate $certificateTemplate, CertificateService $certificateService): \Illuminate\Http\Response
    {
        $this->authorizeOwnership($certificateTemplate);

        $html = $certificateService->renderSample($certificateTemplate);

        return response($html, 200, ['Content-Type' => 'text/html']);
    }
}
