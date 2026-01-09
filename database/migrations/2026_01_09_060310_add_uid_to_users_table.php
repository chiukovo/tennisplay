<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUidToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('uid', 20)->unique()->nullable()->after('id');
        });

        // Generate UID for existing users
        \DB::table('users')->whereNull('uid')->orderBy('id')->each(function ($user) {
            \DB::table('users')->where('id', $user->id)->update([
                'uid' => 'u' . str_pad($user->id, 6, '0', STR_PAD_LEFT)
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uid');
        });
    }
}
