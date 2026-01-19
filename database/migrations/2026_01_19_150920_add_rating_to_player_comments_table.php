<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRatingToPlayerCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('player_comments', function (Blueprint $table) {
            $table->unsignedTinyInteger('rating')->nullable()->after('content')->comment('1-5 star rating');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('player_comments', function (Blueprint $table) {
            $table->dropColumn('rating');
        });
    }
}
