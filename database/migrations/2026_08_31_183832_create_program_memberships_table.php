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
        Schema::create('program_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('capability', 64);
            $table->string('status', 32)->default('active');
            $table->timestampTz('starts_at');
            $table->foreignId('granted_by')->constrained('users')->restrictOnDelete();
            $table->jsonb('stage_scope')->nullable();
            $table->timestampTz('ends_at')->nullable();
            $table->foreignId('ended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('end_reason')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['program_id', 'status']);
            $table->index(['program_id', 'user_id', 'status']);
        });

        DB::statement("create unique index program_memberships_active_capability_unique on program_memberships (program_id, user_id, capability) where status = 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_memberships');
    }
};
