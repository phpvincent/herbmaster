<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAttributeNameAttributeEnglishNameToProductAttributeListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasColumn('product_attribute_list','attribute_value')) {
            Schema::table('product_attribute_list', function (Blueprint $table) {
               $table->string('attribute_value', 32)->change();
            });
        }
        if(Schema::hasColumn('product_attribute_list','attribute_english_value')) {
            Schema::table('product_attribute_list', function (Blueprint $table) {
                $table->string('attribute_english_value', 32)->change();
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
        if(Schema::hasColumn('product_attribute_list','attribute_value')) {
            Schema::table('product_attribute_list', function (Blueprint $table) {
                $table->unsignedInteger('attribute_value')->change();
            });
        }
        if(Schema::hasColumn('product_attribute_list','attribute_english_value')) {
            Schema::table('product_attribute_list', function (Blueprint $table) {
                $table->unsignedInteger('attribute_english_value')->change();
            });
        }
    }
}
