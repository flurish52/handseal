<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->foreignId('programme_id')
                ->constrained('programmes')
                ->restrictOnDelete(); // don't let a programme vanish out from under enrolled students

            $table->string('name');
            $table->string('enrollment_number')->nullable(); // business's own reference number, not globally unique
            $table->string('phone')->nullable();
            $table->date('start_at');
            $table->date('end_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->enum('status', ['active', 'completed'])->default('active'); // manually overridable any time

            $table->timestamps();

            $table->index(['business_id', 'name']); // owner searching their own students
            $table->index('phone');
            $table->unique(['business_id', 'enrollment_number']); // unique within a business, not globally
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
