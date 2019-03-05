<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdminIdToSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('sites', 'admin_id')) {
            Schema::table('sites', function (Blueprint $table) {
                $table->integer('admin_id')->index()->after('is_open')->comment('站点建立人员');
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
        if(Schema::hasColumn('sites', 'admin_id')) {
            Schema::table('sites', function (Blueprint $table) {
                $table->dropColumn('admin_id');
            });
        }
    }
}
