<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('referrer_user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('referred_user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('reward_percent')->default(25); // snapshot at referral time, in case the rate changes later
            $table->enum('status', ['pending', 'requested', 'paid', 'rejected'])->default('pending');
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index('referrer_user_id');
            $table->index('referred_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
