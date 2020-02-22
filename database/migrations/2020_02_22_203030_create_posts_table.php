<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->unique()->comment('博文标题');
            $table->string('keywords', 255)->default('')->comment('关键词');
            $table->string('description', 255)->default('')->comment('描述');
            $table->string('post_img', 255)->default('')->comment('首图');
            $table->string('banner_img', 255)->default('')->comment('banner长图');
            $table->longText('contents')->comment('内容');
            $table->string('category')->default('')->comment('分类名');// 应该由cat_id设置时自动更新
            $table->integer('cat_id')->default(0)->comment('分类ID');
            $table->unsignedInteger('click')->default(0)->comment('点击次数');
            $table->unsignedInteger('featured')->default(0)->comment('精选的时间');
            $table->tinyInteger('is_show')->default(1)->comment('是否显示');

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
        Schema::dropIfExists('posts');
    }
}
