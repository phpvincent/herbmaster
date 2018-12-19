<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAdminsAdminGroupResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('admins')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->boolean('admin_use')->default(0)->change();
                $table->boolean('is_root')->default(0)->change();
                $table->boolean('admin_method')->default(0)->change();
//                $table->index('is_root');
            });
        }
        if (Schema::hasTable('admin_group')) {
            Schema::table('admin_group', function (Blueprint $table) {
                $table->boolean('group_rule')->default(0)->change();
            });
        }
        if (Schema::hasTable('resources')) {
            Schema::table('resources', function (Blueprint $table) {
               $table->decimal('size',10,2)->default(0)->change();
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
        //
    }
}
