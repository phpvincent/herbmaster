<?php

/**
 *  后台路由文件
 */

Route::namespace('Admin')->group(function(){
    Route::any('captcha','LoginController@captcha');  //获取登陆验证码信息
});

