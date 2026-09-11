<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Programme;
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
                ...$templateSelection, // certificate_template_id
            ]);

            $certificate->refresh(); // pick up certificate_number set by the created() hook

            $qrPath = $this->qrCodeService->generateForCertificate($certificate);
            $certificate->update(['qr_path' => $qrPath]);

            return $certificate;
        });
    }

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
                'certificate_template_id' => $data['certificate_template_id'],
            ]);

            $certificate->refresh();

            $qrPath = $this->qrCodeService->generateForCertificate($certificate);
            $certificate->update(['qr_path' => $qrPath]);

            return $certificate;
        });
    }

    /**
     * Renders the certificate to a downloadable PDF. Never calls Gemini here —
     * custom template content was generated once, at template-creation time.
     */
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
            'certificate_template_id' => $templateSelection['certificate_template_id'] ?? null,
        ]);

        $certificate->business_id = $business->id;
        $certificate->setRelation('business', $business);
        $certificate->setRelation('student', $student);
        $certificate->setRelation('programme', $student->programme);

        if ($certificate->certificate_template_id) {
            $template = CertificateTemplate::findOrFail($certificate->certificate_template_id);

            // Preview access is intentionally looser than "use" access:
            // anyone can preview a platform template (builtin or
            // marketplace, purchased or not) to decide whether to buy it.
            // Only another business's private custom draft stays blocked.
            abort_unless(
                $template->business_id === null || $template->business_id === $business->id,
                403
            );

            $certificate->setRelation('certificateTemplate', $template);
        }

        return $this->buildHtml($certificate);
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
            'certificate_template_id' => $data['certificate_template_id'] ?? null,
        ]);


        $certificate->business_id = $business->id;
        $certificate->setRelation('business', $business);
        $certificate->setRelation('programme', $programme);

        if ($certificate->certificate_template_id) {
            $template = CertificateTemplate::findOrFail($certificate->certificate_template_id);

            // Same relaxed rule as renderPreview() above — preview access
            // doesn't require ownership/purchase, only "use" (activate)
            // does. Otherwise nobody could preview a premium marketplace
            // template before buying it.
            abort_unless(
                $template->business_id === null || $template->business_id === $business->id,
                403
            );

            $certificate->setRelation('certificateTemplate', $template);
        }

        return $this->buildHtml($certificate);
    }

    /**
     * Renders a template with fully synthetic data — no real Business, Student,
     * or QR involved. Used for marketplace thumbnails, where the image is shared
     * across every business rather than personalized to one. Reuses
     * renderCustomContent() directly since every template (builtin or custom)
     * now lives in certificate_templates with the same content/Blade shape.
     */
    public function renderSample(CertificateTemplate $template): string
    {
        $certificate = new Certificate([
            'recipient_name' => 'Sample Recipient',
            'start_date' => now()->subMonths(6),
            'end_date' => now(),
            'certificate_number' => 'SAMPLE-000000',
            'is_guest' => true,
        ]);

        $certificate->certificate_template_id = $template->id;
        $certificate->setRelation('business', new Business(['business_name' => 'Sample Business']));
        $certificate->setRelation('programme', new Programme(['name' => 'Sample Programme']));
        $certificate->setRelation('certificateTemplate', $template);

        // qr_path stays unset on purpose — qrBase64() returns null cleanly,
        // and builtin/custom views already guard the QR block with @if/??.

        return $this->renderCustomContent($certificate);
    }

    private function buildHtml(Certificate $certificate): string
    {
        return $this->renderCustomContent($certificate);
    }

    private function renderCustomContent(Certificate $certificate): string
    {
        return Blade::render(
            $certificate->certificateTemplate->content,
            ['certificate' => $certificate]
        );
    }
}
