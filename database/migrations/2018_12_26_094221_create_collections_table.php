<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   if (! Schema::hasTable('collections')) {
            Schema::create('collections', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->unsignedTinyInteger('status')->default(0)->comment('集合状态');
                $table->integer('site_id')->index()->comment('集合所属站点id');
                $table->text('description')->nullable();
                $table->integer('img')->comment('集合对应图片');
                $table->string('template_type')->nullable()->comment("模板类型");
                $table->string('remark')->nullable()->comment('备注');
                $table->timestamp('start_time')->useCurrent()->comment('启用时间');
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('collections_products')) {
            Schema::create('collections_products', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('collections_id')->comment('集合id');
                $table->integer('products_id')->comment('产品id');
                $table->string('remark')->comment('备注');
                $table->integer('sort')->default(0)->comment('排序');
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
        Schema::dropIfExists('collections');
        Schema::dropIfExists('collections_products');
    }
}
