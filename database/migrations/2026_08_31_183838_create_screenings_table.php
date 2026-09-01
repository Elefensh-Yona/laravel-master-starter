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
        Schema::create('screenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->restrictOnDelete();
            $table->foreignId('application_id')->constrained('applications')->restrictOnDelete();
            $table->foreignId('application_version_id')->constrained('application_versions')->restrictOnDelete();
            $table->foreignId('validation_id')->nullable()->constrained('application_validations')->nullOnDelete();
            $table->string('status', 32)->default('in_review');
            $table->string('outcome', 32)->nullable();
            $table->foreignId('screened_by')->constrained('users')->restrictOnDelete();
            $table->timestampTz('completed_at');
            $table->text('rationale');
            $table->timestampTz('reopened_at')->nullable();
            $table->foreignId('reopened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reopen_reason')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'status']);
            $table->index(['program_id', 'outcome']);
            $table->index(['screened_by', 'completed_at']);
        });

        DB::statement("ALTER TABLE screenings ADD CONSTRAINT screenings_status_check CHECK (status IN ('in_review', 'completed'))");
        DB::statement("ALTER TABLE screenings ADD CONSTRAINT screenings_outcome_check CHECK (outcome IS NULL OR outcome IN ('ELIGIBLE', 'INELIGIBLE'))");
        DB::statement("CREATE UNIQUE INDEX screenings_completed_application_version_unique ON screenings (application_version_id) WHERE status = 'completed'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screenings');
    }
};
