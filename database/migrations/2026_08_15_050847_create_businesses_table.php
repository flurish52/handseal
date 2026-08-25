<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('business_name');
            $table->string('trade_category')->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_publicly_visible')->default(true);
            $table->string('logo_path')->nullable();
            $table->string('cert_prefix', 6)->nullable()->unique();
            $table->unsignedTinyInteger('ai_rejection_count')
                ->default(0);
            $table->unsignedTinyInteger('ai_attempts_remaining')
                ->default(3);
            $table->string('default_builtin_template_key')->nullable();
            $table->timestamps();

            $table->index('business_name');
            $table->index('is_publicly_visible');
            $table->timestamp('subscription_active_until')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
