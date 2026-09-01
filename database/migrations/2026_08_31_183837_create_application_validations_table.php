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
        Schema::create('application_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->restrictOnDelete();
            $table->foreignId('application_id')->constrained('applications')->restrictOnDelete();
            $table->foreignId('application_version_id')->constrained('application_versions')->restrictOnDelete();
            $table->string('status', 32)->default('passed');
            $table->jsonb('result')->nullable();
            $table->timestampTz('executed_at');
            $table->foreignId('executed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'status']);
            $table->index(['program_id', 'executed_at']);
        });

        DB::statement("ALTER TABLE application_validations ADD CONSTRAINT application_validations_status_check CHECK (status IN ('passed', 'failed', 'error'))");
        DB::statement('CREATE UNIQUE INDEX application_validations_version_program_unique ON application_validations (application_version_id, program_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_validations');
    }
};
