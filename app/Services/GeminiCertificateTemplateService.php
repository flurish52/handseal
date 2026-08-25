<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiCertificateTemplateService
{
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX_IMAGE_BYTES = 5 * 1024 * 1024; // 5MB
    private const MAX_IMAGES = 3;
    private const REFUSAL_MARKER = 'NOT_A_CERTIFICATE:';

    /**
     * Generates a full HTML/Blade-compatible certificate template from a business's
     * description of what they want, optionally guided by reference images.
     * Called exactly once per request — never at print time.
     * The returned content is stored as a 'draft' and must be reviewed before going live.
     *
     * @param  UploadedFile[]  $images
     *
     * @throws InvalidCertificateReferenceException if the model determines the
     *         reference image(s) don't actually depict a certificate/award design.
     */
    public function generate(string $businessName, string $description, array $images = [],  string $sampleType = 'template',): string
    {
        $apiKey = config('services.gemini.key');

        if (! $apiKey) {
            throw new RuntimeException('Gemini API key not configured (services.gemini.key).');
        }


        $imageParts = $this->buildImageParts($images);
        $prompt = $this->buildPrompt($businessName, $description, $imageParts, $sampleType);

        $response = Http::withHeaders([
            'x-goog-api-key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post(
            sprintf(
                'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
                config('services.gemini.model')
            ),
            [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            ...$imageParts,
                        ],
                    ],
                ],
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException('Gemini request failed: ' . $response->body());
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

        if (! $text) {
            throw new RuntimeException('Gemini returned no usable content.');
        }

        $text = trim($text);

        if (str_starts_with($text, self::REFUSAL_MARKER)) {
            $reason = trim(substr($text, strlen(self::REFUSAL_MARKER)));

            throw new InvalidCertificateReferenceException(
                $reason !== ''
                    ? $reason
                    : 'The attached image doesn\'t look like a certificate or award design. Try a clearer sample, or describe the design in words instead.'
            );
        }

        return $this->stripCodeFences($text);
    }

    /**
     * @param  UploadedFile[]  $images
     * @return array<int, array<string, mixed>>
     */
    private function buildImageParts(array $images): array
    {
        if (count($images) > self::MAX_IMAGES) {
            throw new RuntimeException('Too many reference images (max ' . self::MAX_IMAGES . ').');
        }

        $parts = [];

        foreach ($images as $image) {
            if (! $image instanceof UploadedFile || ! $image->isValid()) {
                throw new RuntimeException('One of the uploaded images is invalid.');
            }

            if ($image->getSize() > self::MAX_IMAGE_BYTES) {
                throw new RuntimeException('One of the uploaded images exceeds the size limit.');
            }

            $mime = $image->getMimeType();

            if (! in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
                throw new RuntimeException("Unsupported image type: {$mime}.");
            }

            $parts[] = [
                'inlineData' => [
                    'mimeType' => $mime,
                    'data' => base64_encode(file_get_contents($image->getRealPath())),
                ],
            ];
        }

        return $parts;
    }

    private function buildPrompt(string $businessName, string $description, array $imageParts, string $sampleType): string
    {
        $imageInstructions = '';

        if (! empty($imageParts)) {
            $imageInstructions = $sampleType === 'hardcopy'
                ? $this->hardcopyInstructions()
                : $this->templateInstructions();
        }

        return <<<PROMPT
        Generate a single self-contained HTML certificate template for a business
        named "{$businessName}". It will be rendered by DomPDF, so these constraints are
        non-negotiable:
        - No flexbox, no CSS grid, no external stylesheets or fonts.
        - No `clip-path` of any kind — DomPDF does not support it and will silently render
          a plain rectangle instead, discarding the shape entirely. For triangular, chevron,
          arrow, or angled-corner shapes, use the zero-width bordered-triangle technique
          instead: a 0×0-sized div with `border-style: solid`, transparent borders on the
          sides you don't want visible, and a solid color on the one side that should form
          the triangle. Combine multiple such triangles/rectangles to build chevrons, arrows,
          or angled corners. This is a well-supported DomPDF technique — use it instead of
          clip-path for every angled shape in the reference.
        - Use table-based or absolute-positioned layout only.
        - A4 landscape page size via @page { size: A4 landscape; margin: 0; }.
        - Include these Blade placeholders exactly as written, do not rename them:
          {{ \$certificate?->business?->business_name }}, {{ \$certificate?->recipient_name }},
          {{ \$certificate?->programme?->name }}, {{ \$certificate->start_date->format('d M Y') }},
          {{ \$certificate->end_date->format('d M Y') }}, {{ \$certificate->certificate_number }},
          and an <img> tag with src="{{ \$certificate?->qrBase64() }}
          Guard every variable/field properly with ? or ?? to prevent errors".

        {$imageInstructions}

        Style direction from the business owner: {$description}

        If everything checks out, return ONLY the HTML, no explanation, no markdown code fences.
        PROMPT;
    }

    private function templateInstructions(): string
    {
        return <<<IMG
        A reference image is attached. First confirm it actually depicts a certificate,
        diploma, award, or similarly formal achievement document. If it does NOT, do not
        guess — respond with EXACTLY this and nothing else:

        NOT_A_CERTIFICATE: <one short sentence explaining what the image actually shows>

        If it IS a valid certificate design, note that many reference images are PRODUCT
        MOCKUPS: the certificate is shown as a card/sheet resting on an unrelated surface
        (a wood or fabric table, a plain colored backdrop) with a drop shadow and sometimes
        a slight tilt. That surrounding surface, shadow, and tilt are STAGING — presentation
        for a product photo, not part of the certificate's own design. Ignore them
        completely: do not use the table/backdrop color or texture as the page background,
        and do not add any shadow or tilt to the output.

        Identify only the certificate/card itself — its own edges and everything printed or
        designed within those edges. THAT inner design is what you must reproduce, and this
        is a fidelity task, not loose inspiration: match every distinct visual element you
        can identify — overall background color(s), corner/edge geometry and shapes (using
        the bordered-triangle technique above where needed), accent colors, any seal, badge,
        or ribbon graphic, divider lines, and the text layout/hierarchy (heading, "presented
        to" line, recipient line, body text, date/signature lines). Do not omit an element
        because it's hard to reproduce — approximate it with the closest DomPDF-safe
        technique rather than dropping it. Do not add decorative elements that aren't
        present in the reference either.

        The one exception: wherever the reference shows specific filled-in text (a
        recipient's name, a business name, dates, a certificate number, etc.), replace it
        with the corresponding required Blade placeholder above, in the same position and
        style — never copy the literal text from the image.
        IMG;
    }

    private function hardcopyInstructions(): string
    {
        return <<<IMG
        A reference image is attached. It is a PHOTOGRAPH of a physical certificate that
        has already been issued to a real person — it may be skewed, folded, have glare or
        shadows, sit on a table/background, or include a hand or phone edge in frame. All of
        that — the surface it's resting on, glare, shadows, skew, tilt — is incidental to the
        photo, not part of the certificate's design. Ignore it completely.

        First confirm the underlying subject is actually a certificate, diploma, or award
        document. If it clearly is NOT, respond with EXACTLY this and nothing else:

        NOT_A_CERTIFICATE: <one short sentence explaining what the image actually shows>

        If it IS a certificate, look past the photo artifacts and identify only the
        underlying PRINTED DESIGN: background color(s), border/corner geometry (using the
        bordered-triangle technique above where needed), seal/ribbon placement, color
        scheme, and typography style. Reproduce that design faithfully — match every
        distinct visual element you can identify rather than simplifying it away.

        Do NOT copy any of the real filled-in details visible in the photo — the actual
        recipient's name, dates, certificate number, signature, or phone number belong to a
        real person and must never appear in the output. Replace every such spot with the
        corresponding required Blade placeholder above, in the same position and style.
        IMG;
    }

    private function stripCodeFences(string $html): string
    {
        return trim(preg_replace('/^```(?:html)?|```$/m', '', $html));
    }
}
