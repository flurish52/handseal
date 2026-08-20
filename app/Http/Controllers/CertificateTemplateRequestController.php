<?php
namespace App\Http\Controllers;

use App\Http\Requests\RequestCertificateTemplateRequest;
use App\Models\CertificateTemplateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CertificateTemplateRequestController extends Controller
{
    public function store(RequestCertificateTemplateRequest $request): RedirectResponse
    {
        $business = Auth::user()->businesses()->firstOrFail();
        $images = $request->file('images', []);

        $storedImages = collect($images)->map(function (UploadedFile $image) use ($business) {
            $path = $image->store("certificate-template-requests/{$business->id}", 'local');

            return [
                'path' => $path,
                'original_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getClientMimeType(),
            ];
        })->all();

        $business->certificateTemplateRequests()->create([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'sample_type' => $request->validated('sample_type', 'template'),
            'images' => $storedImages,
            'status' => 'pending',
        ]);

        return back()->with('success', "Request submitted. We'll design it and let you know when it's ready to review.");
    }

    // A pending request the business changed their mind about, or a declined
    // one they don't want to resubmit. Completed requests can't be cancelled —
    // delete the template instead, from CertificateTemplateController::destroy.
    public function destroy(CertificateTemplateRequest $certificateTemplateRequest): RedirectResponse
    {
        $this->authorizeOwnership($certificateTemplateRequest);

        abort_if(
            $certificateTemplateRequest->status === 'completed',
            422,
            'This request already has a template — delete the template instead.'
        );

        foreach ($certificateTemplateRequest->images ?? [] as $stored) {
            Storage::disk('local')->delete($stored['path']);
        }

        $certificateTemplateRequest->delete();

        return back()->with('success', 'Request cancelled.');
    }

    private function authorizeOwnership(CertificateTemplateRequest $certificateTemplateRequest): void
    {
        abort_unless(
            $certificateTemplateRequest->business_id === Auth::user()->businesses()->firstOrFail()->id,
            403
        );
    }
}
