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
        Route::get('permission','LoginController@permission');  //管理员登陆返回用户权限

        //资源操作
        Route::post('resource/upload','ResourcesController@upload');
        Route::get('resource/get_file_list','ResourcesController@get_file_list');
        Route::get('resource/get_file_by_id','ResourcesController@get_file_by_id');
        Route::get('resource/get_filepath_by_id','ResourcesController@get_filepath_by_id');
        Route::delete('resource/del_file_by_id','ResourcesController@del_file_by_id');
        Route::delete('resource/del_file_by_ids','ResourcesController@del_file_by_ids');

//        Route::middleware(['admin_permission'])->group(function() {
            //角色操作
            Route::get('role/index', 'RoleController@index');      //角色列表
            Route::post('role/add', 'RoleController@add');         //添加角色
            Route::put('role/store', 'RoleController@store');      //修改角色
            Route::get('role/show', 'RoleController@show');        //修改角色
            Route::delete('role/delete', 'RoleController@destory');//删除角色

            //管理员
            Route::get('admin/admins', 'AdminController@index');
            Route::get('admin/info', 'AdminController@info');
            Route::get('admin/my', 'AdminController@my');
            Route::post('admin/add', 'AdminController@add');
            Route::put('admin/edit', 'AdminController@edit');
            Route::put('admin/change_password', 'AdminController@changePassword');
            Route::delete('admin/delete', 'AdminController@destory');

            Route::get('admin_group/all', 'AdminGroupController@all');
            Route::get('admin_group/info', 'AdminGroupController@info');
            Route::post('admin_group/add', 'AdminGroupController@add');
            Route::put('admin_group/edit', 'AdminGroupController@edit');
            Route::delete('admin_group/delete', 'AdminGroupController@destory');
            //权限操作
            Route::get('permission/index', 'PermissionController@index');    //权限列表
            Route::post('permission/add', 'PermissionController@add');       //添加权限或者修改权限
            Route::get('permission/show', 'PermissionController@show');      //获取角色权限
            //集合操作
            Route::get('collection/index', 'CollectionController@index');
            Route::get('collection/collections_products', 'CollectionController@collections_products');
            Route::put('collection/set_collection_status', 'CollectionController@set_collection_status');
            Route::put('collection/set_collection_start_time', 'CollectionController@set_collection_start_time');
            Route::put('collection/update', 'CollectionController@update');
            Route::delete('collection/destory', 'CollectionController@destory');
            Route::post('collection/upload', 'CollectionController@upload');
            Route::post('collection/add_products', 'CollectionController@add_products');
            Route::delete('collection/del_products', 'CollectionController@del_products');

            //产品
            Route::get('attribute/all', 'AttributeController@index');
            Route::post('attribute/add', 'AttributeController@add');
            Route::put('attribute/edit', 'AttributeController@edit');
            Route::delete('attribute/delete', 'AttributeController@destory');
            Route::get('product/index', 'ProductController@index');
            Route::post('product/add', 'ProductController@add');
            Route::put('product/edit', 'ProductController@edit');
            Route::delete('product/destory', 'ProductController@destory');
            Route::get('product/info', 'ProductController@info');
            Route::post('product/variant/add', 'ProductController@add_variant');
            Route::put('product/variant/edit', 'ProductController@edit_variant');
            Route::post('product/option/add', 'ProductController@add_option');
            Route::delete('product/tag/delete', 'ProductController@delete_tags');
            Route::delete('supplier/delete', 'SupplierController@destory');
            Route::put('supplier/edit', 'SupplierController@edit');
            Route::get('product_type/all', 'ProductTypeController@index');
            Route::put('product/resources/edit', 'ProductController@edit_resources');
            Route::delete('product/resources/delete', 'ProductController@del_resources');
            Route::get('product/by_conditions', 'ProductController@get_products_by_conditions');
            //标签
            Route::post('tag/add', 'TagController@add');
            Route::get('tag/index', 'TagController@index');
            Route::delete('tag/delete', 'TagController@destory');

            //库存
            Route::get('inventory/index', 'InventoryController@index');
            Route::get('inventory/show', 'InventoryController@show');
            Route::put('inventory/edit', 'InventoryController@edit');
            //站点
            Route::get('site/index', 'SiteController@index');
            Route::get('site/get_site_info', 'SiteController@get_site_info');
            Route::put('site/update', 'SiteController@update');
    });
//    });

});

