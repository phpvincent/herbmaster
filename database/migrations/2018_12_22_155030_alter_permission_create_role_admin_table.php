<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPermissionCreateRoleAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('role_user');

        if (! Schema::hasTable('role_admins')) {
            Schema::create('role_admins', function (Blueprint $table) {
                $table->integer('admin_id')->unsigned();
                $table->integer('role_id')->unsigned();
                $table->foreign('admin_id')->references('id')->on('admins')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('role_id')->references('id')->on('roles')
                    ->onUpdate('cascade')->onDelete('cascade');
                $table->primary(['admin_id', 'role_id']);
            });
        }

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

    }
}
