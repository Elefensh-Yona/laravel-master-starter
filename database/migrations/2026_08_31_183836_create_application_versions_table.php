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
        Schema::create('application_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->restrictOnDelete();
            $table->integer('version_number');
            $table->string('status', 32)->default('draft');
            $table->jsonb('content');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestampTz('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('revision_reason')->nullable();
            $table->foreignId('supersedes_version_id')->nullable()->constrained('application_versions')->nullOnDelete();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'status']);
        });

        DB::statement('ALTER TABLE application_versions ADD CONSTRAINT application_versions_version_number_check CHECK (version_number > 0)');
        DB::statement('CREATE UNIQUE INDEX application_versions_application_number_unique ON application_versions (application_id, version_number)');

        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('current_version_id')->nullable()->after('submitted_at')->constrained('application_versions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_versions');
    }
};
