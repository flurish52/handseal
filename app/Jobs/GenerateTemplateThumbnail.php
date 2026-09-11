<?php

namespace App\Jobs;

use App\Models\CertificateTemplate;
use App\Services\CertificateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateTemplateThumbnail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(private CertificateTemplate $template)
    {
    }

    public function handle(CertificateService $certificateService): void
    {
        $html = $certificateService->renderSample($this->template);

        $imageBytes = $this->renderHtmlToImage($html);

        if ($imageBytes === null) {
            Log::warning('Template thumbnail generation failed, leaving thumbnail_path null.', [
                'template_id' => $this->template->id,
            ]);

            return; // gallery falls back to a generic placeholder image at display time
        }

        // Name includes a content hash so a stale cached/CDN copy is never served
        // after the template's content changes and this job re-runs.
        $hash = substr(md5($this->template->content), 0, 12);
        $path = "template-thumbnails/{$this->template->id}-{$hash}.png";

        Storage::disk('public')->put($path, $imageBytes);

        $this->template->update(['thumbnail_path' => $path]);
    }

    /**
     * Renders HTML to a PNG via htmlcsstoimage.com — no Ghostscript/Chromium
     * needed on this server, which matters since we're on shared hosting.
     * Swap this method's body for a Browsershot call later if/when moving to
     * a VPS with root access; nothing outside this method needs to change.
     */
    private function renderHtmlToImage(string $html): ?string
    {
        $userId = config('services.htmlcsstoimage.user_id');
        $apiKey = config('services.htmlcsstoimage.api_key');

        if (! $userId || ! $apiKey) {
            Log::error('htmlcsstoimage credentials not configured (services.htmlcsstoimage.*).');

            return null;
        }

        $response = Http::withBasicAuth($userId, $apiKey)
            ->timeout(30)
            ->post('https://hcti.io/v1/image', [
                'html' => $html,
                'viewport_width' => 1123,  // ~A4 landscape at 96dpi
                'viewport_height' => 794,
            ]);

        if ($response->failed()) {
            Log::error('htmlcsstoimage request failed.', [
                'template_id' => $this->template->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $imageUrl = $response->json('url');

        if (! $imageUrl) {
            Log::error('htmlcsstoimage response had no image url.', [
                'template_id' => $this->template->id,
            ]);

            return null;
        }

        // The image renders on first fetch — retry a few times in case it's
        // not quite ready yet, rather than failing on a single slow render.
        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $imageResponse = Http::timeout(20)->get($imageUrl);

            if ($imageResponse->successful() && strlen($imageResponse->body()) > 0) {
                return $imageResponse->body();
            }

            sleep($attempt); // brief backoff: 1s, 2s, 3s, 4s
        }

        Log::error('htmlcsstoimage image never became available after retries.', [
            'template_id' => $this->template->id,
            'url' => $imageUrl,
        ]);

        return null;
    }
}
