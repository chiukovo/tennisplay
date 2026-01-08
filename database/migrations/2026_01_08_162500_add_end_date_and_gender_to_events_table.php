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
        Schema::table('events', function (Blueprint $table) {
            // Add end_date column after event_date
            $table->dateTime('end_date')->nullable()->after('event_date');
            
            // Add gender restriction
            $table->enum('gender', ['all', 'male', 'female'])->default('all')->after('match_type');
        });
        
        // Modify match_type to include 'all' option and change default
        // Note: MySQL doesn't support modifying ENUM easily, so we'll handle this in application logic
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['end_date', 'gender']);
        });
    }
};
