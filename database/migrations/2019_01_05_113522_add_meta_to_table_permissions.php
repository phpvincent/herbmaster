<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMetaToTablePermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('permissions','meta')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->string('meta', 255)->default('')->after('display_name');
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
        if(Schema::hasColumn('permissions','meta')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->dropColumn('meta');
            });
        }
    }
}
