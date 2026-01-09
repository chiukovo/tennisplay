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
        // Follows table
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('following_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['follower_id', 'following_id']);
        });

        // Likes table (for player cards)
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['user_id', 'player_id']);
        });

        // Enforce unique player per user
        Schema::table('players', function (Blueprint $table) {
            // First, we might need to clean up duplicates if any existed, 
            // but user said "目前會清除資料" (clear data for now) or "沒有多卡用戶".
            // To be safe and clean, we'll just add the unique index.
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Disable foreign key checks before dropping the unique index
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Drop unique index from players table
        Schema::table('players', function (Blueprint $table) {
            $table->dropUnique('players_user_id_unique');
        });
        
        // Re-enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        Schema::dropIfExists('likes');
        Schema::dropIfExists('follows');
    }
};
