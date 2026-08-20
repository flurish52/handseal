<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_template_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certificate_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description');
            $table->string('sample_type')->default('template'); // template | hardcopy
            $table->json('images')->nullable(); // [{path, original_name, mime_type}]
            $table->string('status')->default('pending'); // pending | in_review | completed | declined
            $table->text('admin_note')->nullable(); // admin's message when declining, or internal notes
            $table->timestamp('reviewed_at')->nullable(); // when admin last actioned it (claimed/generated/declined)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_template_requests');
    }
};
