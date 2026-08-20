<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programmes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->string('name');
            $table->decimal('price', 10, 2)->nullable();
            $table->string('typical_duration')->nullable();
            $table->boolean('is_archived')->default(false);

            $table->timestamps();

            $table->index('business_id');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programmes');
    }
};
