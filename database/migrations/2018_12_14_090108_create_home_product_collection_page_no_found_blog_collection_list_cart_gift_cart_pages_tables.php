<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHomeProductCollectionPageNoFoundBlogCollectionListCartGiftCartPagesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('theme_home')) {
            Schema::create('theme_home', function (Blueprint $table) {
               $table->increments('id');
               $table->unsignedInteger('site_id')->default(0)->index()->comment('站点id');
               $table->unsignedInteger('theme_id')->default(0)->index()->comment('主题id');
               $table->unsignedInteger('admin_id')->default(0)->comment('编辑人');
               $table->string('type')->default('')->comment('页面类型标记');
               $table->json('content')->default(null)->comment('主题数据');
               $table->timestamps();
            });
        }
        if (! Schema::hasTable('theme_product')) {
            Schema::create('theme_product', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('site_id')->default(0)->index()->comment('站点id');
                $table->unsignedInteger('theme_id')->default(0)->index()->comment('主题id');
                $table->unsignedInteger('admin_id')->default(0)->comment('编辑人');
                $table->string('type')->default('')->comment('页面类型标记');
                $table->json('content')->default(null)->comment('主题数据');
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('theme_collection_page')) {
            Schema::create('theme_collection_page', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('site_id')->default(0)->index()->comment('站点id');
                $table->unsignedInteger('theme_id')->default(0)->index()->comment('主题id');
                $table->unsignedInteger('admin_id')->default(0)->comment('编辑人');
                $table->string('type')->default('')->comment('页面类型标记');
                $table->json('content')->default(null)->comment('主题数据');
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('theme_not_found')) {
            Schema::create('theme_not_found', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('site_id')->default(0)->index()->comment('站点id');
                $table->unsignedInteger('theme_id')->default(0)->index()->comment('主题id');
                $table->unsignedInteger('admin_id')->default(0)->comment('编辑人');
                $table->string('type')->default('')->comment('页面类型标记');
                $table->json('content')->default(null)->comment('主题数据');
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('theme_blog')) {
            Schema::create('theme_blog', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('site_id')->default(0)->index()->comment('站点id');
                $table->unsignedInteger('theme_id')->default(0)->index()->comment('主题id');
                $table->unsignedInteger('admin_id')->default(0)->comment('编辑人');
                $table->string('type')->default('')->comment('页面类型标记');
                $table->json('content')->default(null)->comment('主题数据');
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('theme_collection_list')) {
            Schema::create('theme_collection_list', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('site_id')->default(0)->index()->comment('站点id');
                $table->unsignedInteger('theme_id')->default(0)->index()->comment('主题id');
                $table->unsignedInteger('admin_id')->default(0)->comment('编辑人');
                $table->string('type')->default('')->comment('页面类型标记');
                $table->json('content')->default(null)->comment('主题数据');
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('theme_cart_gift')) {
            Schema::create('theme_cart_gift', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('site_id')->default(0)->index()->comment('站点id');
                $table->unsignedInteger('theme_id')->default(0)->index()->comment('主题id');
                $table->unsignedInteger('admin_id')->default(0)->comment('编辑人');
                $table->string('type')->default('')->comment('页面类型标记');
                $table->json('content')->default(null)->comment('主题数据');
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('theme_page')) {
            Schema::create('theme_page', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('site_id')->default(0)->index()->comment('站点id');
                $table->unsignedInteger('theme_id')->default(0)->index()->comment('主题id');
                $table->unsignedInteger('admin_id')->default(0)->comment('编辑人');
                $table->string('type')->default('')->comment('页面类型标记');
                $table->json('content')->default(null)->comment('主题数据');
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('theme_cart')) {
            Schema::create('theme_cart', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('site_id')->default(0)->index()->comment('站点id');
                $table->unsignedInteger('theme_id')->default(0)->index()->comment('主题id');
                $table->unsignedInteger('admin_id')->default(0)->comment('编辑人');
                $table->string('type')->default('')->comment('页面类型标记');
                $table->json('content')->default(null)->comment('主题数据');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('theme_home');
        Schema::dropIfExists('theme_product');
        Schema::dropIfExists('theme_collection_page');
        Schema::dropIfExists('theme_not_found');
        Schema::dropIfExists('theme_blog');
        Schema::dropIfExists('theme_collection_list');
        Schema::dropIfExists('theme_cart_gift');
        Schema::dropIfExists('theme_page');
        Schema::dropIfExists('theme_cart');

    }
}
