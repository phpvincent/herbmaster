<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPermissionRoleUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('role_user')) {
            Schema::table('role_user', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('admins')
                    ->onUpdate('cascade')->onDelete('cascade');
            });
        };

        if (Schema::hasTable('permission')) {
            Schema::table('permission', function (Blueprint $table) {
                $table->string('meta')->nullable()->comment('前台路由meta');
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
