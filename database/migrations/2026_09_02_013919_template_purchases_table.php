<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_purchases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('certificate_template_id')
                ->constrained('certificate_templates')
                ->cascadeOnDelete();

            // Snapshot the price paid at purchase time — don't rely on the
            // template's current `price` column, since that can change later
            // and this row is your purchase-history source of truth.
            $table->unsignedInteger('price_paid');

            // e.g. 'wallet' or 'paystack' — matches whatever your existing
            // Paystack/wallet flow already uses for payment_method elsewhere.
            $table->string('payment_method');

            $table->timestamp('purchased_at');
            $table->timestamps();

            // A business shouldn't be able to buy the same template twice —
            // once purchased, access is permanent (non-exclusive, one-time).
            $table->unique(['business_id', 'certificate_template_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_purchases');
    }
};
