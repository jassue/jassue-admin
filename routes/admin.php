<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => 'Common',
    'middleware' => ['record-log', 'auth:admin']
], function () {
    Route::post('/excel_upload', 'UploadController@excelUpload');
    Route::post('/image_upload', 'UploadController@imageUpload');
    Route::get('/excel_template', 'DownloadController@excelTemplate');
});

Route::group([
    'namespace' => 'Admin',
    'middleware' => 'record-log'
], function () {
    Route::post('auth/login', 'AuthController@login');

    Route::group(['middleware' => 'auth:admin'], function () {
        Route::group([
            'prefix' => 'auth'
        ], function () {
            Route::post('logout', 'AuthController@logout');
            Route::post('info', 'AuthController@info');
            Route::post('update_profile', 'StaffController@updatePersonalInfo');
            Route::post('set_password', 'AdminController@setPassword');
        });

        // 组件
        Route::group([
            'prefix' => 'widget'
        ], function () {
            Route::post('list_dept', 'DepartmentController@list');
            Route::post('list_staff', 'StaffController@list');
            Route::post('list_role', 'AdminController@getRoleList');
        });

        // 系统管理
        Route::group([
            'prefix' => 'system'
        ], function () {
            Route::post('setting/detail', 'SettingController@detail')->middleware('can:SYSTEM_CONFIG');
            Route::post('setting/update', 'SettingController@update')->middleware('can:SYSTEM_CONFIG');

            Route::post('log/list', 'SystemLogController@list')->middleware('can:SYSTEM_LOG');
        });

        // 通讯录
        Route::group([
            'middleware' => 'can:STAFF_DEPT_VIEW'
        ], function () {
            Route::post('/staff/list', 'StaffController@list')->middleware('can:STAFF_DEPT_VIEW');
            Route::post('/staff/excel_import', 'StaffController@excelImport')->middleware('can:STAFF_CREATE');
            Route::post('/staff/create', 'StaffController@create')->middleware('can:STAFF_CREATE');
            Route::post('/staff/set_dept', 'StaffController@setDept')->middleware('can:STAFF_UPDATE');
            Route::post('/staff/update', 'StaffController@update')->middleware('can:STAFF_UPDATE');
            Route::post('/staff/delete', 'StaffController@delete')->middleware('can:STAFF_DELETE');

            Route::post('/dept/list', 'DepartmentController@list')->middleware('can:STAFF_DEPT_VIEW');
            Route::post('/dept/create', 'DepartmentController@create')->middleware('can:DEPT_CREATE');
            Route::post('/dept/update_name', 'DepartmentController@updateName')->middleware('can:DEPT_UPDATE');
            Route::post('/dept/delete', 'DepartmentController@delete')->middleware('can:DEPT_DELETE');
        });

        // 管理员
        Route::group([
            'middleware' => 'can:ADMIN_ROLE_VIEW',
        ], function () {
            Route::post('/role/permissions', 'AdminController@getPermissionList')->middleware('can:ADMIN_ROLE_VIEW');
            Route::post('/role/list', 'AdminController@getRoleList')->middleware('can:ADMIN_ROLE_VIEW');
            Route::post('/role/create', 'AdminController@roleCreate')->middleware('can:ROLE_CREATE');
            Route::post('/role/update', 'AdminController@roleUpdate')->middleware('can:ROLE_UPDATE');
            Route::post('/role/delete', 'AdminController@roleDelete')->middleware('can:ROLE_DELETE');

            Route::post('/admin/list', 'AdminController@adminList')->middleware('can:ADMIN_ROLE_VIEW');
            Route::post('/admin/create', 'AdminController@create')->middleware('can:ADMIN_CREATE');
            Route::post('/admin/update', 'AdminController@update')->middleware('can:ADMIN_UPDATE');
            Route::post('/admin/delete', 'AdminController@delete')->middleware('can:ADMIN_DELETE');
            Route::post('/admin/reset_password', 'AdminController@resetPassword')->middleware('can:ADMIN_UPDATE');
        });

    });
});
