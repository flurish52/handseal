<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCertificateRequest;
use App\Models\Certificate;
use App\Models\Student;
use App\Services\CertificateEligibilityService;
use App\Services\CertificateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{

    public function index(?Student $student = null): Response
    {
        $business = Auth::user()->businesses()->firstOrFail();

        $selectedStudent = null;

        if ($student) {
            $selectedStudent = $business->students()
                ->where('status', 'completed')
                ->where('id', $student->id)
                ->select(['id', 'name', 'programme_id'])
                ->first();
        }

        return Inertia::render('Certificates/Index', [
            'certificates' => $business->certificates()->with('student', 'programme')->latest()->get(),
            'selectedStudent' => $selectedStudent,
            'builtins' => [
                ['key' => 'classic-navy', 'label' => 'Classic Navy & Brass'],
            ],
            'customTemplates' => $business->certificateTemplates()->where('status', 'active')->get(['id', 'name']),
        ]);
    }

    public function store(StoreCertificateRequest $request, CertificateService $certificateService, CertificateEligibilityService $eligibility): RedirectResponse
    {
        $business = Auth::user()->businesses()->firstOrFail();
        $student = Student::findOrFail($request->validated('student_id'));

        $check = $eligibility->check($business);

        if (! $check['allowed']) {
            return back()->with([
                'error' => $check['message'],
                'paywall' => $check['reason'],
            ]);
        }

        $student->fill([
            'end_at' => $student->end_at ?? now(),
            'completed_at' => $student->completed_at ?? now(),
        ])->save();

        $certificate = $certificateService->issueForStudent($business, $student, $request->safe()->only([
            'builtin_template_key',
            'certificate_template_id',
        ]));

        return back()->with([
            'success' => 'Certificate issued.',
            'download_url' => route('certificates.download', $certificate->id),
        ]);
    }

    public function preview(Request $request): \Illuminate\Http\Response
    {
        $business = Auth::user()->businesses()->firstOrFail();
        $student = Student::where('business_id', $business->id)
            ->findOrFail($request->query('student_id'));

        $html = app(CertificateService::class)->renderPreview($business, $student, [
            'builtin_template_key' => $request->query('builtin_template_key'),
            'certificate_template_id' => $request->query('certificate_template_id'),
        ]);

        return response($html, 200, ['Content-Type' => 'text/html']);
    }

    public function previewGuest(Request $request): \Illuminate\Http\Response
    {
        $business = Auth::user()->businesses()->firstOrFail();

        $html = app(CertificateService::class)->renderGuestPreview($business, [
            'recipient_name' => $request->query('recipient_name'),
            'programme_id' => $request->query('programme_id'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'builtin_template_key' => $request->query('builtin_template_key'),
            'certificate_template_id' => $request->query('certificate_template_id'),
        ]);

        return response($html, 200, ['Content-Type' => 'text/html']);
    }

    public function download(Certificate $certificate, CertificateService $certificateService)
    {
        abort_unless(
            $certificate->business_id === Auth::user()->businesses()->firstOrFail()->id,
            403
        );

        return $certificateService->renderPdf($certificate)
            ->download("{$certificate->recipient_name}-{$certificate->certificate_number}.pdf");
    }
}
