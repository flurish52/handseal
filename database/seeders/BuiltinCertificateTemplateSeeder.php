<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\CertificateTemplate;

class BuiltinCertificateTemplateSeeder extends Seeder
{
    /**
     * Adjust price (kobo) and is_premium per template once you've decided
     * pricing. Left as a starting point — free vs premium split is a guess,
     * not a decision I made for you.
     */
    protected array $builtins = [
        [
            'key' => 'classic-navy',
            'name' => 'Classic Navy & Brass',
            'is_premium' => false,
            'price' => 0,
        ],
        [
            'key' => 'executive-sidebar',
            'name' => 'Executive Sidebar',
            'is_premium' => true,
            'price' => 150000, // ₦1,500 — placeholder, adjust
        ],
        [
            'key' => 'portrait-minimal',
            'name' => 'Minimal Modern',
            'is_premium' => false,
            'price' => 0,
        ],
        [
            'key' => 'ornate-gold',
            'name' => 'Ornate Gold Seal',
            'is_premium' => true,
            'price' => 150000,
        ],
        [
            'key' => 'portrait-formal',
            'name' => 'Formal Report',
            'is_premium' => true,
            'price' => 100000,
        ],
    ];

    public function run(): void
    {
        foreach ($this->builtins as $sortOrder => $builtin) {
            $viewPath = resource_path("views/certificates/builtin/{$builtin['key']}.blade.php");

            if (! File::exists($viewPath)) {
                $this->command->warn("Skipped '{$builtin['key']}' — view not found at {$viewPath}");
                continue;
            }

            // Raw file contents, NOT the rendered view — this is Blade markup
            // that gets compiled fresh with real $certificate data at issuance
            // time, same as AI-generated templates.
            $content = File::get($viewPath);

            CertificateTemplate::updateOrCreate(
                [
                    // business_id = null + name is enough to identify a builtin
                    // row uniquely for idempotent re-seeding.
                    'business_id' => null,
                    'name' => $builtin['name'],
                ],
                [
                    'content' => $content,
                    'status' => 'active',
                    'source' => 'builtin',
                    'certificate_template_request_id' => null,
                    'is_premium' => $builtin['is_premium'],
                    'price' => $builtin['price'],
                    'thumbnail_path' => null, // filled in by the thumbnail generation job, not here
                    'is_active' => true,
                    'sort_order' => $sortOrder,
                ]
            );
        }

        $this->command->info('Built-in certificate templates seeded.');
    }
}
