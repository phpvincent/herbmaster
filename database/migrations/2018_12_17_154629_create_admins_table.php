<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->increments('admin_id');
            $table->string('admin_name', 100)->comment('账号');
            $table->string('password', 100)->comment('密码');
            $table->string('admin_show_name', 255)->comment('管理员真实名称');
            $table->string('is_root', 2)->default('0')->comment('是否是超级管理员，0不是，1是');
            $table->rememberToken();
            $table->string('admin_ip', 100)->nullable()->comment('登陆ip');
            $table->dateTime('admin_time')->nullable()->comment('登陆时间');
            $table->integer('admin_num')->default(0)->comment('登陆次数');
            $table->string('admin_use',4)->default('0')->comment('是否启用,1:开启,0:关闭');
            $table->integer('admin_group')->comment('所属分组');
            $table->tinyInteger('admin_method')->default('0')->comment('管理员读写权限');
            $table->tinyInteger('admin_is_order')->default(0)->comment('是否查看订单完整信息，0：不查看 1：查看');
            $table->tinyInteger('admin_data_rule')->default(0)->comment('查看数据权限 :0：仅查看自己，1：查看自己与本组，2：查看全体成员，3：root');
            $table->tinyInteger('admin_languages')->default(0)->comment('管理员查看语言权限（真对翻译人员特定的权限）0：所以权限；1：繁体中文,2：阿拉伯语,3：马来语,4：泰语,5：日语,6：印尼语,7：英语');
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
        Schema::dropIfExists('admins');
    }
}
