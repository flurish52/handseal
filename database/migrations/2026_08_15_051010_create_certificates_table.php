<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            $table->foreignId('student_id')
                ->nullable()
                ->constrained('students')
                ->nullOnDelete(); // null for guest certs (is_guest = true)

            $table->foreignId('certificate_template_id')
                ->nullable()
                ->constrained('certificate_templates')
                ->nullOnDelete(); // set when a custom template was used

            $table->string('builtin_template_key')->nullable(); // set when one of the 5 built-in presets was used instead

            $table->foreignId('programme_id')
                ->constrained('programmes')
                ->restrictOnDelete(); // a programme can't be deleted once certificates reference it

            // Unique per-cert number, generated AFTER insert: HS-{INITIALS}{BUSINESS_ID}-{CERT_ID}
            // Nullable at the DB level only because it's filled in the split second after the row
            // is inserted (need the id first) — application code should never leave it null after that.
            $table->string('certificate_number')->nullable()->unique();

            // Denormalized on purpose: a guest recipient has no student row to pull a name from,
            // and even for tracked students, a certificate should keep saying the name as it was
            // issued rather than silently updating if the student record changes later.
            $table->string('recipient_name');

            $table->date('start_date');
            $table->date('end_date');

            $table->boolean('is_guest')->default(false);

            $table->string('qr_path')->nullable();
            $table->timestamp('issued_at')->nullable();

            $table->timestamps();

            $table->index('business_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
