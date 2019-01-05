<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPermissionAddMetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasColumn('permission','meta')) {
            Schema::table('permission', function (Blueprint $table) {
                $table->string('meta', 255)->change();
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
        if(Schema::hasColumn('permission','meta')) {
            Schema::table('permission', function (Blueprint $table) {
                $table->string('meta')->change();
            });
        }
    }
}
