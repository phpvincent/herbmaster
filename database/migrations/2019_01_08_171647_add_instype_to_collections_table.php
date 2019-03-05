<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInstypeToCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('collections', 'instype')){
            Schema::table('collections', function (Blueprint $table) {
                $table->integer('instype')->index()->default(0)->after('status')->comment('0：手动选择产品，1：自动选择产品');
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
        if(Schema::hasColumn('collections', 'instype')) {
            Schema::table('collections', function (Blueprint $table) {
                $table->dropColumn('instype');
            });
        }
    }
}
