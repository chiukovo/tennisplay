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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 主辦人
            $table->foreignId('player_id')->constrained()->onDelete('cascade'); // 主辦人的球員卡
            $table->string('title'); // 活動標題
            $table->dateTime('event_date'); // 活動日期時間
            $table->string('location'); // 球場名稱
            $table->string('address')->nullable(); // 球場地址
            $table->decimal('fee', 8, 0)->default(0); // 每人費用
            $table->unsignedTinyInteger('max_participants')->default(4); // 人數上限
            $table->enum('match_type', ['singles', 'doubles', 'mixed'])->default('doubles'); // 單打/雙打/混雙
            $table->string('level_min')->nullable(); // 最低程度限制
            $table->string('level_max')->nullable(); // 最高程度限制
            $table->text('notes')->nullable(); // 備註
            $table->enum('status', ['open', 'full', 'closed', 'completed', 'cancelled'])->default('open');
            $table->timestamps();
            
            $table->index(['event_date', 'status']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
