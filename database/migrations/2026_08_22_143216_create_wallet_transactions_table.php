<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // credit | debit
            $table->integer('amount'); // always positive; direction comes from `type`
            $table->integer('balance_after');
            $table->string('reason'); // subscription_renewal | topup | guest_purchase | cert_issued | referral_reward | rollover_expire | custom_cert_fee
            $table->json('meta')->nullable(); // e.g. { "subscription_id": 4, "certificate_id": 91 }
            $table->timestamps();

            $table->index(['wallet_id', 'reason']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
