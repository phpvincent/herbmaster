<?php

/**
 *  后台路由文件
 */

Route::namespace('Admin')->group(function(){
    Route::any('captcha','LoginController@captcha');  //获取登陆验证码信息
    Route::post('login','LoginController@login');  //管理员登陆

    Route::middleware(['admin_refresh','log'])->group(function(){
        Route::post('user/user_info','UserController@userInfo');
    });
});

