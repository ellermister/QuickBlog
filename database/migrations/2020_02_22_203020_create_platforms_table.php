<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlatformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('platforms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name',100)->default('')->unique()->comment('平台英文名称');
            $table->string('title',100)->default('')->comment('平台显示名称');
            $table->string('describe',100)->default('')->comment('平台描述');
            $table->string('img',255)->default('')->comment('图标');
            $table->text('cookie');
            $table->text('account');
            $table->integer('created_at');
            $table->integer('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('platforms');
    }
}
