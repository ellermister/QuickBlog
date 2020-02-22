<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesUnionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories_union', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('platform_id')->default(0)->comment('平台ID');
            $table->integer('site_cat_id')->default(0)->comment('站点分类ID');
            $table->string('platform_cat_id')->default("")->comment('平台分类ID');
            $table->string('platform_cat_name')->default("")->comment('平台分类名称');
            $table->unique(['platform_id', 'site_cat_id'], 'unique_id');
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
        Schema::dropIfExists('categories_union');
    }
}
