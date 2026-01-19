<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReplyToPlayerCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('player_comments', function (Blueprint $table) {
            $table->text('reply')->nullable()->after('rating');
            $table->timestamp('replied_at')->nullable()->after('reply');
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
            $table->dropColumn(['reply', 'replied_at']);
        });
    }
}
