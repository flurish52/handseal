<?php
// app/Http/Controllers/Admin/CertificateTemplateRequestController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplateRequest;
use App\Services\GeminiCertificateTemplateService;
use App\Services\InvalidCertificateReferenceException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class CertificateTemplateRequestController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-certificate-requests'); // TODO: swap for your real admin gate

        $requests = CertificateTemplateRequest::with('business:id,business_name')
            ->whereIn('status', ['pending', 'in_review', 'declined'])
            ->latest()
            ->get();

        return Inertia::render('Admin/CertificateTemplateRequests/Index', [
            'requests' => $requests,
        ]);
    }

    // Admin opens a request to start working on it.
    public function claim(CertificateTemplateRequest $certificateTemplateRequest): RedirectResponse
    {
        Gate::authorize('manage-certificate-requests');

        $certificateTemplateRequest->update(['status' => 'in_review']);

        return back()->with('success', 'Marked as in review.');
    }

    // Admin triggers the actual AI generation once they're ready — this is the
    // ONLY place GeminiCertificateTemplateService gets called from now.
    public function generate(CertificateTemplateRequest $certificateTemplateRequest, GeminiCertificateTemplateService $gemini): RedirectResponse
    {
        Gate::authorize('manage-certificate-requests');

        abort_if($certificateTemplateRequest->status === 'completed', 422, 'This request already has a template.');

        $business = $certificateTemplateRequest->business;

        $images = collect($certificateTemplateRequest->images ?? [])
            ->map(fn (array $stored) => new UploadedFile(
                Storage::disk('local')->path($stored['path']),
                $stored['original_name'],
                $stored['mime_type'],
                null,
                true
            ))
            ->all();

        try {
            $content = $gemini->generate(
                businessName: $business->business_name,
                description: $certificateTemplateRequest->description,
                images: $images,
                sampleType: $certificateTemplateRequest->sample_type,
            );
        } catch (InvalidCertificateReferenceException $e) {
            return back()->withErrors(['generate' => $e->getMessage()]);
        } catch (Throwable $e) {
            report($e);
            return back()->withErrors(['generate' => 'Generation failed — check the reference images and try again.']);
        }

        $template = $business->certificateTemplates()->create([
            'name' => $certificateTemplateRequest->name,
            'content' => $content,
            'status' => 'draft',
        ]);

        $certificateTemplateRequest->update([
            'status' => 'completed',
            'certificate_template_id' => $template->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', "Draft created for {$business->business_name} — they can now review and approve it.");
    }

    // Admin decides they can't fulfill this one — always with a reason, so the
    // business gets something actionable instead of a silent dead end.
    public function decline(Request $request, CertificateTemplateRequest $certificateTemplateRequest): RedirectResponse
    {
        Gate::authorize('manage-certificate-requests');

        $request->validate([
            'admin_note' => ['required', 'string', 'max:1000'],
        ]);

        $certificateTemplateRequest->update([
            'status' => 'declined',
            'admin_note' => $request->string('admin_note'),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Request declined with feedback.');
    }
}
