<?php

/**
 *  后台路由文件
 */

Route::namespace('Admin')->group(function(){
    Route::any('captcha','LoginController@captcha');  //获取登陆验证码信息
    Route::post('login','LoginController@login');  //管理员登陆

    Route::middleware(['admin_refresh','log'])->group(function(){
        Route::post('user/user_info','UserController@userInfo');
        //资源操作
        Route::post('resource/upload','ResourcesController@upload');
        Route::get('resource/get_file_list','ResourcesController@get_file_list');
        Route::get('resource/get_file_by_id','ResourcesController@get_file_by_id');
        Route::get('resource/get_filepath_by_id','ResourcesController@get_filepath_by_id');

        //角色操作
        Route::get('role/index','RoleController@index');    //列表
        Route::post('role/add','RoleController@add');       //添加角色
        Route::put('role/upload','RoleController@store');   //修改角色
        Route::delete('role/delete','RoleController@destory');//删除角色

        //权限操作
    });
});

