<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSitesNavsDomainsResourcesPostagesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('sites')) {
            Schema::create('sites',function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 64)->default('')->comment('站点名称');
                $table->string('title', 128)->default('')->comment('站点标题');
                $table->unsignedTinyInteger('is_open')->default(0)->comment('是否开启，0-否，1-开启');
                $table->unsignedInteger('domain_id')->default(0)->index()->comment('域名id');
                $table->string('lock_prompt', 255)->default('')->comment('锁站提示');
                $table->string('secret', 32)->default('')->comment('秘钥');
                $table->string('shield_area', 255)->default('')->comment('屏蔽地区字段,以;为间隔');
                $table->string('shield_ip', 255)->default('')->comment('屏蔽ip字段,以;为间隔');
                $table->string('theme',16)->default('')->comment('主题');
                $table->string('email', 64)->default('')->comment('邮箱');
                $table->string('order_prefix', 32)->default('')->comment('订单前缀');
                $table->unsignedInteger('currency_id')->default(0)->comment('货币id');
                $table->string('fb_pix', 255)->default('')->comment('Facebook像素');
                $table->string('google_pix', 255)->default('')->comment('谷歌像素');
                $table->string('time_difference', 16)->default('')->comment('时差');
                $table->string('payment_ids', 64)->default('')->comment('支付方式id，多种支付方式用,隔开');
                $table->unsignedTinyInteger('fill_checkout_info')->default(0)->comment('如何填充订单信息来源，0-自动填充，1-填充部分信息，2-用户手动填写');
                $table->unsignedTinyInteger('abandoned_checkout_email_time_delay')->default(0)->comment('用户放弃结账邮件发送延时，单位小时');
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('navs')) {
            Schema::create('navs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('site_id')->default(0)->index()->comment('站点id');
                $table->string('name', 64)->comment('导航标题');
                $table->unsignedInteger('parent_id')->default(0)->comment('父导航id');
                $table->string('vue_route', 64)->default('')->comemnt('对应vue路由');
                $table->unsignedTinyInteger('sort')->default(100)->comment('排序');
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('domains')) {
            Schema::create('domains', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 64)->comment('域名名称');
                $table->unsignedInteger('site_id')->default(0)->index()->comment('站点id');
                $table->unsignedTinyInteger('is_marked')->default(0)->comment('是否被标记，0-未被标记，1-被标记');
                $table->string('marked_platform', 32)->default('')->comment('被标记的平台，多个平台用,隔开');
                $table->timestamp('expiry_time')->useCurrent()->comment('域名过期时间');
                $table->string('owner')->default('')->comment('域名所属人');
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('resources')) {
            Schema::create('resources', function (Blueprint $table){
                $table->increments('id');
                $table->unsignedInteger('site_id')->default(0)->index()->comment('站点id');
                $table->unsignedSmallInteger('cate_id')->default(0)->index()->comment('类型id');
                $table->unsignedInteger('admin_id')->default(0)->index()->comment('上传者id');
                $table->string('type', 32)->defult('')->comment('文档类型');
                $table->unsignedSmallInteger('size')->default(0)->comment('资源大小');
                $table->string('path',  255)->default('')->comment('路径');
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('postages')){
            Schema::create('postages', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('site_id')->index()->default(0)->comment('站点id');
                $table->enum('calculation_method',['number', 'weight', 'volume', 'other'])->comment('计算方式');
                $table->json('content')->default(null)->comment('内容');
                $table->timestamps();
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
        Schema::dropIfExists('sites');
        Schema::dropIfExists('navs');
        Schema::dropIfExists('domains');
        Schema::dropIfExists('resources');
        Schema::dropIfExists('postages');
    }
}