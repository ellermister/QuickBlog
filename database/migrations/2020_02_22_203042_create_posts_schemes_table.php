<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsSchemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts_schemes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('post_id')->default(0)->comment('博文ID');
            $table->integer('platform_id')->default(0)->comment('平台ID');
            $table->tinyInteger('sync_status')->default(0)->comment('0:就绪 1:等待同步 2:同步完成 3:同步失败');
            $table->string('third_id')->default(0)->comment('第三方平台文章ID');
            $table->string('third_url')->default(0)->comment('第三方平台文章URL');
            $table->unique(['post_id', 'platform_id'], 'unique_id');
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
        Schema::dropIfExists('posts_schemes');
    }
}
