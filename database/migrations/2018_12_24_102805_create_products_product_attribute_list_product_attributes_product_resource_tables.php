<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsProductAttributeListProductAttributesProductResourceTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('site_id')->index()->comment('站点id');
                $table->string('name', 64)->comment('产品名称，展示 使用');
                $table->string('name', 64)->comment('产品英文名称');
                $table->text('description')->comment('描述');
                $table->decimal('price', 10, 2)->default(0)->comment('价格');
                $table->decimal('original_price', 10, 2)->default(0)->comment('原价');
                $table->decimal('cost_price', 10, 2)->default(0)->comment('成本价');
                $table->string('sku', 32)->default('')->nullable()->comment('sku码');
                $table->string('bar_code', 32)->default('')->nullable()->comment('条形码');
                $table->unsignedTinyInteger('is_reduce_invenory')->default(0)->comment('用户下单是否减库存');
                $table->integer('num')->default(0)->comment('库存数量');
                $table->unsignedTinyInteger('is_physical_product')->default(0)->comment('是否为物理产品');
                $table->decimal('weight', 10, 2)->default(0)->comment('重量');
                $table->unsignedInteger('type')->default(0)->comment('分类');
                $table->unsignedTinyInteger('collection_id')->default(0)->comment('页面集合id');
                $table->unsignedInteger('admin_id')->default(0)->index()->comment('管理员id');
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('attributes')) {
            Schema::create('attributes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 32)->comment('属性名称，展示使用');
                $table->string('english_name', 32)->comment('属性英文名称');
            });
        }
        if (!Schema::hasTable('product_attribute_list')) {
            Schema::create('product_attribute_list', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('product_id')->index()->comment('产品id');
                $table->unsignedInteger('attribute_id')->index()->comment('属性id');
                $table->unsignedInteger('attribute_value')->comment('属性值，展示使用');
                $table->unsignedInteger('attribute_english_value')->comment('英文属性值');
            });
        }
        if (!Schema::hasTable('product_attributes')) {
            Schema::create('product_attributes', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('product_id')->index()->comment('产品id');
                $table->string('attribute_list_ids')->default('')->comment('产品属性列表ids,多个id用,隔开');
                $table->decimal('price', 10, 2)->default(0)->comment('价格');
                $table->string('sku', 32)->default('')->nullable()->comment('sku码');
                $table->string('bar_code', 32)->default('')->nullable()->comment('条形码');
                $table->integer('num')->default(0)->comment('数量');
            });
        }
        if (!Schema::hasTable('product_resource')) {
            Schema::create('product_resource', function (Blueprint $table) {
                $table->unsignedInteger('product_id')->default(0)->index()->comment('产品id');
                $table->unsignedInteger('resource_id')->default(0)->index()->comment('资源id');
                $table->unsignedTinyInteger('is_index')->default(0)->comment('是否首图，1-是，2-不是');
                $table->unsignedTinyInteger('sort')->default(0)->comment('排序');
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
        Schema::dropIfExists('products');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('product_attribute_list');
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('product_resource');
    }
}
