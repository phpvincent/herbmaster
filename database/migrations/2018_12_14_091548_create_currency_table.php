<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCurrencyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('currency')) {
            Schema::create('currency', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->comment('货币名称');
                $table->string('english_name')->default('')->comment('货币英文名称');
                $table->decimal('exchange_rate',10,6)->default(0)->comment('汇率');
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
        Schema::dropIfExists('currency');
    }
}
