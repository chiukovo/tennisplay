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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            
            // Basic Info
            $table->string('name');
            $table->string('photo')->nullable();
            $table->string('region')->default('台北市');
            $table->string('level')->default('3.5');
            $table->string('gender')->default('男');
            $table->string('handed')->default('右手');
            $table->string('backhand')->default('雙反');
            $table->text('intro')->nullable();
            $table->string('fee')->default('免費 (交流為主)');
            
            // Signature
            $table->text('signature')->nullable();
            $table->string('theme')->default('standard');
            
            // Photo positioning
            $table->float('photo_x')->default(0);
            $table->float('photo_y')->default(0);
            $table->float('photo_scale')->default(1);
            
            // Signature positioning
            $table->float('sig_x')->default(0);
            $table->float('sig_y')->default(0);
            $table->float('sig_scale')->default(1);
            $table->float('sig_rotate')->default(0);
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            
            $table->timestamps();
            
            // Indexes
            $table->index('region');
            $table->index('level');
            $table->index('gender');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
