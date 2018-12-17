<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_group', function (Blueprint $table) {
            $table->increments('admin_group_id');
            $table->string('admin_group_name', 255)->comment('组名');
            $table->string('admin_group_rule', 4)->default('0')->comment('是否特殊权限 0：普通分组 1：特殊分组');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_group');
    }
}
