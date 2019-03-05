<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameToresourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('resources', 'name')) {
            Schema::table('resources', function (Blueprint $table) {
                $table->string('name', 198)->index()->comment('资源文件名');
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
        if(Schema::hasColumn('resources', 'name')) {
            Schema::table('resources', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }
}
