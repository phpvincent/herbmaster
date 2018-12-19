<?php

/**
 *  后台路由文件
 */

Route::namespace('Admin')->group(function(){
    Route::any('captcha','LoginController@captcha');  //获取登陆验证码信息
    Route::post('login','LoginController@login');  //管理员登陆

    Route::middleware(['admin_refresh','log'])->group(function(){
        Route::post('user/user_info','UserController@userInfo');
        Route::put('resource/upload','ResourcesController@upload');

        //角色操作
        Route::get('role/index','RoleController@index');    //列表
        Route::post('role/add','RoleController@add');       //添加角色
        Route::put('role/upload','RoleController@store');   //修改角色
        Route::delete('role/delete','RoleController@destory');//删除角色

        //权限操作
    });
});

