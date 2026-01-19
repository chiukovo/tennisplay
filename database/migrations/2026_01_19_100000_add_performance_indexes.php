<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 效能優化：為高頻查詢欄位建立索引
     * 使用 IF NOT EXISTS 邏輯避免重複建立
     */
    public function up()
    {
        // Players 表索引
        $this->addIndexIfNotExists('players', 'is_active', 'idx_players_is_active');
        $this->addIndexIfNotExists('players', 'region', 'idx_players_region');
        $this->addIndexIfNotExists('players', 'level', 'idx_players_level');
        $this->addIndexIfNotExists('players', 'gender', 'idx_players_gender');
        $this->addIndexIfNotExists('players', 'updated_at', 'idx_players_updated_at');
        $this->addCompositeIndexIfNotExists('players', ['is_active', 'region', 'level'], 'idx_players_active_region_level');

        // LINE 通知日誌索引
        if (Schema::hasTable('line_notification_logs')) {
            $this->addIndexIfNotExists('line_notification_logs', 'user_id', 'idx_line_logs_user_id');
            $this->addIndexIfNotExists('line_notification_logs', 'status', 'idx_line_logs_status');
            $this->addIndexIfNotExists('line_notification_logs', 'created_at', 'idx_line_logs_created_at');
        }
    }

    /**
     * 新增單一欄位索引（如果不存在）
     */
    private function addIndexIfNotExists($table, $column, $indexName)
    {
        if (!$this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $t) use ($column, $indexName) {
                $t->index($column, $indexName);
            });
        }
    }

    /**
     * 新增複合索引（如果不存在）
     */
    private function addCompositeIndexIfNotExists($table, $columns, $indexName)
    {
        if (!$this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                $t->index($columns, $indexName);
            });
        }
    }

    /**
     * 檢查索引是否存在
     */
    private function indexExists($table, $indexName)
    {
        $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($result) > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $this->dropIndexIfExists('players', 'idx_players_is_active');
        $this->dropIndexIfExists('players', 'idx_players_region');
        $this->dropIndexIfExists('players', 'idx_players_level');
        $this->dropIndexIfExists('players', 'idx_players_gender');
        $this->dropIndexIfExists('players', 'idx_players_updated_at');
        $this->dropIndexIfExists('players', 'idx_players_active_region_level');

        if (Schema::hasTable('line_notification_logs')) {
            $this->dropIndexIfExists('line_notification_logs', 'idx_line_logs_user_id');
            $this->dropIndexIfExists('line_notification_logs', 'idx_line_logs_status');
            $this->dropIndexIfExists('line_notification_logs', 'idx_line_logs_created_at');
        }
    }

    /**
     * 刪除索引（如果存在）
     */
    private function dropIndexIfExists($table, $indexName)
    {
        if ($this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $t) use ($indexName) {
                $t->dropIndex($indexName);
            });
        }
    }
};
