<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE screenings ALTER COLUMN completed_at DROP NOT NULL');
        DB::statement('ALTER TABLE screenings ALTER COLUMN rationale DROP NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE screenings ALTER COLUMN completed_at SET NOT NULL');
        DB::statement('ALTER TABLE screenings ALTER COLUMN rationale SET NOT NULL');
    }
};
