<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 效能優化：為高頻查詢欄位建立索引
     */
    public function up()
    {
        // Players 表索引
        Schema::table('players', function (Blueprint $table) {
            // 活動狀態篩選 - 幾乎每個查詢都用到
            $table->index('is_active', 'idx_players_is_active');
            // 地區篩選
            $table->index('region', 'idx_players_region');
            // 等級篩選
            $table->index('level', 'idx_players_level');
            // 性別篩選
            $table->index('gender', 'idx_players_gender');
            // 更新時間排序
            $table->index('updated_at', 'idx_players_updated_at');
            // 複合索引：常見的篩選組合
            $table->index(['is_active', 'region', 'level'], 'idx_players_active_region_level');
        });

        // Messages 表已在原始 migration 中建立索引，跳過

        // LINE 通知日誌索引
        if (Schema::hasTable('line_notification_logs')) {
            Schema::table('line_notification_logs', function (Blueprint $table) {
                // 檢查索引是否已存在再建立
                if (!$this->hasIndex('line_notification_logs', 'idx_line_logs_user_id')) {
                    $table->index('user_id', 'idx_line_logs_user_id');
                }
                if (!$this->hasIndex('line_notification_logs', 'idx_line_logs_status')) {
                    $table->index('status', 'idx_line_logs_status');
                }
                if (!$this->hasIndex('line_notification_logs', 'idx_line_logs_created_at')) {
                    $table->index('created_at', 'idx_line_logs_created_at');
                }
            });
        }
    }

    /**
     * 檢查索引是否存在
     */
    private function hasIndex($table, $indexName)
    {
        $conn = Schema::getConnection();
        $dbSchemaManager = $conn->getDoctrineSchemaManager();
        $indexes = $dbSchemaManager->listTableIndexes($table);
        return array_key_exists($indexName, $indexes);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropIndex('idx_players_is_active');
            $table->dropIndex('idx_players_region');
            $table->dropIndex('idx_players_level');
            $table->dropIndex('idx_players_gender');
            $table->dropIndex('idx_players_updated_at');
            $table->dropIndex('idx_players_active_region_level');
        });

        if (Schema::hasTable('line_notification_logs')) {
            Schema::table('line_notification_logs', function (Blueprint $table) {
                $table->dropIndex('idx_line_logs_user_id');
                $table->dropIndex('idx_line_logs_status');
                $table->dropIndex('idx_line_logs_created_at');
            });
        }
    }
};

