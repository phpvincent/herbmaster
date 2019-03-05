<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropCollectionIdToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasColumn('products','collection_id')){
            Schema::table('products', function (Blueprint $table){
                $table->dropColumn('collection_id');
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
        if(!Schema::hasColumn('products','collection_id')){
            Schema::table('products', function (Blueprint $table){
                $table->unsignedInteger('collection_id')->default(0)->comment('页面集合id');
            });
        }
    }
}
