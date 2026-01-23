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
        Schema::table('players', function (Blueprint $table) {
            $table->boolean('is_coach')->default(false)->after('is_verified');
            $table->unsignedInteger('coach_price_min')->nullable()->after('is_coach');
            $table->unsignedInteger('coach_price_max')->nullable()->after('coach_price_min');
            $table->string('coach_price_note')->nullable()->after('coach_price_max');
            $table->string('coach_methods')->nullable()->after('coach_price_note');
            $table->string('coach_locations')->nullable()->after('coach_methods');
            $table->string('coach_tags')->nullable()->after('coach_locations');
            $table->text('coach_certs')->nullable()->after('coach_tags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn([
                'is_coach',
                'coach_price_min',
                'coach_price_max',
                'coach_price_note',
                'coach_methods',
                'coach_locations',
                'coach_tags',
                'coach_certs',
            ]);
        });
    }
};
