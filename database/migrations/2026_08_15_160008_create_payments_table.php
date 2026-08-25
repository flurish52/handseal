<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->enum('type', ['certificate', 'subscription', 'funding', 'template_fee']);
            $table->unsignedInteger('amount_kobo');
            $table->string('paystack_reference')->nullable()->unique();
            $table->enum('status', ['pending', 'successful', 'failed'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->json('metadata')->nullable();
            $table->string('return_to')->nullable();
            $table->foreignId('certificate_id')->nullable()
                ->constrained('certificates')->nullOnDelete();

            $table->timestamps();

            $table->index('business_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
