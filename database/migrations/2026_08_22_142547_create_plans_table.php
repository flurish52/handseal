<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // Starter, Growth, Unlimited
            $table->string('slug')->unique();        // starter, growth, unlimited
            $table->unsignedBigInteger('price');      // stored in kobo, e.g. 100000 = ₦1,000
            $table->text('description');
            $table->unsignedInteger('included_certs')->nullable(); // null = unlimited
            $table->unsignedTinyInteger('rollover_cap_multiplier')->default(2); // unused credits cap = included_certs * this
            $table->unsignedInteger('extra_cert_price')->nullable(); // kobo cost per cert once included_certs used up (null when unlimited)
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
