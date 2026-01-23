<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->unsignedSmallInteger('coach_experience_years')->nullable()->after('coach_certs');
            $table->string('coach_certifications')->nullable()->after('coach_experience_years');
            $table->string('coach_languages')->nullable()->after('coach_certifications');
            $table->string('coach_availability')->nullable()->after('coach_languages');
            $table->string('coach_teaching_url')->nullable()->after('coach_availability');
        });
    }

    public function down()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn([
                'coach_experience_years',
                'coach_certifications',
                'coach_languages',
                'coach_availability',
                'coach_teaching_url',
            ]);
        });
    }
};
