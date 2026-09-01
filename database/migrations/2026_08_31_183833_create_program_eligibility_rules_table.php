<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('program_eligibility_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->restrictOnDelete();
            $table->string('key', 64);
            $table->string('label');
            $table->string('rule_type', 64);
            $table->jsonb('configuration');
            $table->unsignedInteger('position');
            $table->boolean('is_required')->default(true);
            $table->boolean('is_enabled')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['program_id', 'key']);
            $table->unique(['program_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_eligibility_rules');
    }
};
