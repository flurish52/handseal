<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Certificate;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;

class CertificateService
{
    public function __construct(private QrCodeService $qrCodeService)
    {
    }

    /**
     * Issues a certificate for a tracked student. Guest issuance (step 9) uses
     * a different entry point since there's no Student row to pull from.
     *
     * Flow: insert row (triggers certificate_number generation via the model's
     * created() hook) -> generate QR now that the number exists -> save qr_path.
     */
    public function issueForStudent(Business $business, Student $student, array $templateSelection): Certificate
    {
        return DB::transaction(function () use ($business, $student, $templateSelection) {
            $certificate = $business->certificates()->create([
                'student_id' => $student->id,
                'programme_id' => $student->programme_id,
                'recipient_name' => $student->name,
                'start_date' => $student->start_at,
                'end_date' => $student->end_at ?? now(),
                'is_guest' => false,
                'issued_at' => now(),
                ...$templateSelection, // certificate_template_id OR builtin_template_key
            ]);

            $certificate->refresh(); // pick up certificate_number set by the created() hook

            $qrPath = $this->qrCodeService->generateForCertificate($certificate);
            $certificate->update(['qr_path' => $qrPath]);

            return $certificate;
        });
    }

    /**
     * Renders the certificate to a downloadable PDF. Never calls Gemini here —
     * custom template content was generated once, at template-creation time.
     */
    /**
     * Issues a certificate for an untracked (guest) recipient. No Student row —
     * name comes straight from the form, and student_id stays null.
     */
    public function issueGuest(Business $business, array $data): Certificate
    {
        return DB::transaction(function () use ($business, $data) {
            $certificate = $business->certificates()->create([
                'student_id' => null,
                'programme_id' => $data['programme_id'],
                'recipient_name' => $data['recipient_name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_guest' => true,
                'issued_at' => now(),
                'builtin_template_key' => $data['builtin_template_key'] ?? null,
                'certificate_template_id' => $data['certificate_template_id'] ?? null,
            ]);

            $certificate->refresh();

            $qrPath = $this->qrCodeService->generateForCertificate($certificate);
            $certificate->update(['qr_path' => $qrPath]);

            return $certificate;
        });
    }

    public function renderPdf(Certificate $certificate): \Barryvdh\DomPDF\PDF
    {
        $html = $this->buildHtml($certificate);

        return Pdf::loadHTML($html)->setPaper('a4', 'landscape');
    }

    /**
     * Renders certificate HTML for an in-browser preview, before anything is
     * persisted. Builds an unsaved Certificate instance so the same builtin/custom
     * rendering path in buildHtml() works unchanged — no DB writes, no certificate
     * number assignment, no QR code (nothing exists yet for it to encode).
     */
    public function renderPreview(Business $business, Student $student, array $templateSelection): string
    {
        $certificate = new Certificate([
            'student_id' => $student->id,
            'programme_id' => $student->programme_id,
            'recipient_name' => $student->name,
            'start_date' => $student->start_at,
            'end_date' => $student->end_at ?? now(),
            'is_guest' => false,
            'issued_at' => now(),
            'certificate_number' => 'PREVIEW',
            'builtin_template_key' => $templateSelection['builtin_template_key'] ?? null,
            'certificate_template_id' => $templateSelection['certificate_template_id'] ?? null,
        ]);

        $certificate->business_id = $business->id;
        $certificate->setRelation('business', $business);
        $certificate->setRelation('student', $student);
        $certificate->setRelation('programme', $student->programme);

        if ($certificate->certificate_template_id) {
            $template = $business->certificateTemplates()->findOrFail($certificate->certificate_template_id);
            $certificate->setRelation('certificateTemplate', $template);
        }

        return $this->buildHtml($certificate);
    }

    private function buildHtml(Certificate $certificate): string
    {
        if ($certificate->certificate_template_id) {
            return $this->renderCustomContent($certificate);
        }

        $key = $certificate->builtin_template_key ?: 'classic-navy';

        return view("certificates.builtin.{$key}", [
            'certificate' => $certificate,
        ])->render();
    }


    public function renderGuestPreview(Business $business, array $data): string
    {
        $programme = $business->programmes()->find($data['programme_id']);

        $certificate = new Certificate([
            'student_id' => null,
            'programme_id' => $data['programme_id'],
            'recipient_name' => $data['recipient_name'] ?: 'Sample recipient',
            'start_date' => $data['start_date'] ?: now(),
            'end_date' => $data['end_date'] ?: now(),
            'is_guest' => true,
            'issued_at' => now(),
            'certificate_number' => 'PREVIEW',
            'builtin_template_key' => $data['builtin_template_key'] ?? null,
            'certificate_template_id' => $data['certificate_template_id'] ?? null,
        ]);

        $certificate->business_id = $business->id;
        $certificate->setRelation('business', $business);
        $certificate->setRelation('programme', $programme);

        if ($certificate->certificate_template_id) {
            $template = $business->certificateTemplates()->findOrFail($certificate->certificate_template_id);
            $certificate->setRelation('certificateTemplate', $template);
        }

        return $this->buildHtml($certificate);
    }
    private function renderCustomContent(Certificate $certificate): string
    {
        return Blade::render(
            $certificate->certificateTemplate->content,
            ['certificate' => $certificate]
        );
    }
}
