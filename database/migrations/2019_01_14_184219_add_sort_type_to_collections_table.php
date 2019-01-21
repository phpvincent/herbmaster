<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSortTypeToCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('collections', function (Blueprint $table) {
                $table->string('sort_type',198)->index()->default(0)->after('status')->comment('0：sort字段正序,1:sort字段倒序,2:产品上线时间正序，3：产品上线时间倒序，4：产品价格正序，5：产品价格倒序，6：产品库存正序，7：产品库存倒序');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('collections', function (Blueprint $table) {
                $table->dropColumn('sort_type');
        });
    }
}
