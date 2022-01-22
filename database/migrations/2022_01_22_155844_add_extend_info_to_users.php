<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExtendInfoToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->default('')->comment('头像URL')->after('remember_token');
            $table->string('last_ip')->default('')->comment('最后登录IP')->after('avatar');
            $table->unsignedInteger('last_time')->default(0)->comment('最后登录时间')->after('last_ip');
            $table->unsignedTinyInteger('is_admin')->default(0)->comment('是否是管理员')->after('last_time');
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
            $table->dropColumn('avatar');
            $table->dropColumn('last_ip');
            $table->dropColumn('last_time');
            $table->dropColumn('is_admin');
        });
    }
}
