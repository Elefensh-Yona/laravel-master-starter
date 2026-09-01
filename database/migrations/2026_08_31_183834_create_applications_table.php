<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->restrictOnDelete();
            $table->foreignId('primary_owner_id')->constrained('users')->restrictOnDelete();
            $table->string('applicant_type', 32);
            $table->string('status', 32)->default('draft');
            $table->string('reference', 64)->nullable();
            $table->timestampTz('submitted_at')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index(['program_id', 'status']);
            $table->index(['primary_owner_id', 'status']);
        });

        DB::statement('CREATE UNIQUE INDEX applications_program_reference_unique ON applications (program_id, reference) WHERE reference IS NOT NULL');
        DB::statement("ALTER TABLE applications ADD CONSTRAINT applications_applicant_type_check CHECK (applicant_type IN ('INDIVIDUAL', 'TEAM', 'ORGANIZATION'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
