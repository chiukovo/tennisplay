<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 新增教練相關欄位
     */
    public function up()
    {
        Schema::table('players', function (Blueprint $table) {
            // 教練身份
            $table->boolean('is_coach')->default(false)->after('is_verified');
            
            // 教練詳細資訊
            $table->string('coach_hourly_rate')->nullable()->after('is_coach');        // 時薪或「私訊詢價」
            $table->string('coach_experience')->nullable()->after('coach_hourly_rate'); // 教學年資
            $table->json('coach_tags')->nullable()->after('coach_experience');          // 專長標籤
            $table->text('coach_locations')->nullable()->after('coach_tags');           // 教學場地
            $table->string('coach_certification')->nullable()->after('coach_locations'); // 證照
            $table->text('coach_available_times')->nullable()->after('coach_certification'); // 可教學時段
            
            // 索引
            $table->index('is_coach', 'idx_players_is_coach');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropIndex('idx_players_is_coach');
            $table->dropColumn([
                'is_coach',
                'coach_hourly_rate',
                'coach_experience',
                'coach_tags',
                'coach_locations',
                'coach_certification',
                'coach_available_times',
            ]);
        });
    }
};
