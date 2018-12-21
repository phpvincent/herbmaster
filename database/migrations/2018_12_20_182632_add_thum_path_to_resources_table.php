<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddThumPathToResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(! Schema::hasColumn('resources', 'thum_path')) {
            Schema::table('resources', function (Blueprint $table) {
                $table->string('thum_path')->comment('缩略图路径');
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
        if(Schema::hasColumn('resources', 'thum_path')) {
            Schema::table('resources', function (Blueprint $table) {
                $table->drop_column('thum_path');
            });
        }
    }
}
