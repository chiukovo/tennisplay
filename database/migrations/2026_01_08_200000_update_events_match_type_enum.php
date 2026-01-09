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
        // Allow 'all' as a valid match_type and set it as the default
        DB::statement("ALTER TABLE events MODIFY match_type ENUM('all','singles','doubles','mixed') NOT NULL DEFAULT 'all'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update any record with 'all' to a valid value before reverting the enum
        DB::table('events')->where('match_type', 'all')->update(['match_type' => 'doubles']);

        // Revert to the original enum definition
        DB::statement("ALTER TABLE events MODIFY match_type ENUM('singles','doubles','mixed') NOT NULL DEFAULT 'doubles'");
    }
};
