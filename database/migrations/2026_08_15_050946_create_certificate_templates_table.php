<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only custom (Gemini-generated) templates live here. The 5 built-in presets
        // are Blade views selected by key on the certificate itself — no DB row needed.
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->string('name');
            $table->longText('content'); // Blade/HTML markup generated once by Gemini, then reused with zero AI calls
            $table->enum('status', ['draft', 'active'])->default('draft'); // must be reviewed before going live

            $table->timestamps();

            $table->index('business_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
    }
};
