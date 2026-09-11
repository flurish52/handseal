<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Custom (Gemini-generated) templates AND platform built-ins both live here.
        // business_id = NULL means platform-owned, available to every business.
        // The 5 built-in presets are seeded as rows with business_id = NULL and
        // source = 'builtin' — same rendering pipeline as custom/AI templates,
        // no separate lookup-by-key branch needed anywhere downstream.
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->nullable()
                ->constrained('businesses')
                ->nullOnDelete();

            $table->string('name');
            $table->longText('content'); // raw Blade/HTML markup, rendered fresh at issuance time
            $table->enum('status', ['draft', 'active', 'rejected', 'inactive'])->default('draft');
            $table->string('source')->default('ai'); // 'ai' | 'admin' | 'builtin'
            $table->foreignId('certificate_template_request_id')->nullable()
                ->constrained()->nullOnDelete();

            // Marketplace fields
            $table->boolean('is_premium')->default(false);
            $table->unsignedInteger('price')->default(0); // kobo — match your wallet/Paystack convention
            $table->string('thumbnail_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index('business_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
    }
};
