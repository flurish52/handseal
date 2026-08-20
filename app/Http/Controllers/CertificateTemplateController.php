<?php
// app/Http/Controllers/CertificateTemplateController.php

namespace App\Http\Controllers;

use App\Models\CertificateTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

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
            ->get(['id', 'name', 'status', 'created_at'])
            ->map(fn (CertificateTemplate $template) => [
                'id' => $template->id,
                'name' => $template->name,
                'status' => $template->status,
                'preview_url' => $buildPreviewUrl(['certificate_template_id' => $template->id]),
            ]);

        $requests = $business->certificateTemplateRequests()
            ->whereIn('status', ['pending', 'in_review', 'declined'])
            ->latest()
            ->get(['id', 'name', 'description', 'status', 'admin_note', 'created_at']);

        $builtins = collect([
            ['key' => 'classic-navy', 'label' => 'Classic Navy & Brass'],
        ])->map(fn (array $builtin) => array_merge($builtin, [
            'preview_url' => $buildPreviewUrl(['builtin_template_key' => $builtin['key']]),
        ]));

        return Inertia::render('CertificateTemplates/Index', [
            'templates' => $templates,
            'requests' => $requests,
            'builtins' => $builtins,
        ]);
    }

    public function activate(CertificateTemplate $certificateTemplate): RedirectResponse
    {
        $this->authorizeOwnership($certificateTemplate);

        $certificateTemplate->update(['status' => 'active']);

        return back()->with('success', 'Template activated.');
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
}
