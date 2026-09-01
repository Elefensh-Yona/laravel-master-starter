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
        Schema::create('application_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 32)->default('active');
            $table->timestampTz('joined_at');
            $table->foreignId('approved_by')->constrained('users')->restrictOnDelete();
            $table->timestampTz('ended_at')->nullable();
            $table->foreignId('ended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('end_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['application_id', 'status']);
        });

        DB::statement("create unique index application_members_active_unique on application_members (application_id, user_id) where status = 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_members');
    }
};
