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
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        DB::statement("ALTER TABLE events MODIFY match_type ENUM('all','singles','doubles','mixed') NOT NULL DEFAULT 'all'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        DB::table('events')->where('match_type', 'all')->update(['match_type' => 'doubles']);
        DB::statement("ALTER TABLE events MODIFY match_type ENUM('singles','doubles','mixed') NOT NULL DEFAULT 'doubles'");
    }

};
