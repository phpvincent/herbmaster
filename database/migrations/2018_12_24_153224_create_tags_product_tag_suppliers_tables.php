<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagsProductTagSuppliersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
               $table->increments('id');
               $table->unsignedInteger('site_id')->default(0)->index()->comment('站点id');
               $table->string('name', 32)->comment('名称');
            });
        }
        if (! Schema::hasTable('product_tag')) {
            Schema::create('product_tag', function (Blueprint $table) {
                $table->unsignedInteger('product_id')->default(0)->index()->comment('产品id');
                $table->unsignedInteger('tag_id')->default(0)->index()->comment('标签id');
            });
        }
        if (! Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('product_id');
                $table->string('url', 255)->default('')->comment('链接地址');
                $table->string('contact', 32)->default('')->comment('联系人');
                $table->string('phone', 16)->default('')->comment('电话');
                $table->decimal('price', 10, 2)->default(0)->comment('价格');
                $table->unsignedInteger('num')->default(0)->comment('日供货量');
                $table->string('remark', 255)->default('')->comment('备注');
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
        Schema::dropIfExists('tags');
        Schema::dropIfExists('product_tag');
        Schema::dropIfExists('suppliers');
    }
}
