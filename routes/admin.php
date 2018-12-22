<?php

/**
 *  后台路由文件
 */

Route::namespace('Admin')->group(function(){
    Route::any('captcha','LoginController@captcha');  //获取登陆验证码信息
    Route::post('login','LoginController@login');  //管理员登陆

    Route::middleware(['admin_refresh','log'])->group(function(){
        Route::post('logout','LoginController@logout');
        Route::post('user/user_info','UserController@userInfo');
        //资源操作
        Route::post('resource/upload','ResourcesController@upload');
        Route::get('resource/get_file_list','ResourcesController@get_file_list');
        Route::get('resource/get_file_by_id','ResourcesController@get_file_by_id');
        Route::get('resource/get_filepath_by_id','ResourcesController@get_filepath_by_id');

//        Route::middleware(['admin_permission'])->group(function() {
            //角色操作
            Route::get('role/index', 'RoleController@index');      //角色列表
            Route::post('role/add', 'RoleController@add');         //添加角色
            Route::put('role/store', 'RoleController@store');      //修改角色
            Route::get('role/show', 'RoleController@show');        //修改角色
            Route::delete('role/delete', 'RoleController@destory');//删除角色

            //管理员
            Route::get('admin/admins', 'AdminController@index');
            Route::get('admin/info/{id}', 'AdminController@info');
            Route::get('admin/my', 'AdminController@my');
            Route::post('admin/add', 'AdminController@add');
            Route::put('admin/edit', 'AdminController@edit');
            Route::put('admin/change_password', 'AdminController@changePassword');
            Route::delete('admin/delete/{id}', 'AdminController@destory');

            Route::get('admin_group/all', 'AdminGroupController@all');
            Route::get('admin_group/info/{id}', 'AdminGroupController@info');
            Route::post('admin_group/add', 'AdminGroupController@add');
            Route::put('admin_group/edit', 'AdminGroupController@edit');
            Route::delete('admin_group/delete/{id}', 'AdminGroupController@destory');
            //权限操作
            Route::get('permission/index', 'PermissionController@index');    //权限列表
            Route::post('permission/add', 'PermissionController@add');       //添加权限或者修改权限
            Route::get('permission/show', 'PermissionController@show');      //获取角色权限
//        });
    });
});

