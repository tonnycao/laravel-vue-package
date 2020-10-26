<?php

Route::middleware(['web', 'auth',])->group(function () {
    Route::get('/loading/request', '\FDT\DataLoader\Http\Controllers\DataRequestController@index');
    Route::get('/loading/request/create', '\FDT\DataLoader\Http\Controllers\DataRequestController@create');
    Route::get('/loading/request/{job}', '\FDT\DataLoader\Http\Controllers\DataRequestController@show');
    Route::get('/loading/request/subtype/{type}', '\FDT\DataLoader\Http\Controllers\DataRequestController@subtype');
    Route::post('/loading/request', '\FDT\DataLoader\Http\Controllers\DataRequestController@store');
    Route::get('/loading/request/file/{file}', '\FDT\DataLoader\Http\Controllers\DataRequestController@download');
    Route::post('/loading/schedule', '\FDT\DataLoader\Http\Controllers\SystemScheduleController@store');
    Route::get('/loading/index', '\FDT\DataLoader\Http\Controllers\SystemScheduleController@index');
    Route::put('/loading/schedule/{sysConfig}', '\FDT\DataLoader\Http\Controllers\SystemScheduleController@update');
    Route::post('/loading/schedule/disable/{sys_config}',
        '\FDT\DataLoader\Http\Controllers\SystemScheduleController@delete');
    Route::post('/loading/request/approve/{job}', '\FDT\DataLoader\Http\Controllers\DataRequestController@update');
    Route::post('/loading/request/{job}/disable', '\FDT\DataLoader\Http\Controllers\DataExceptionController@store');
    Route::post('/loading/request/{e}/approve', '\FDT\DataLoader\Http\Controllers\DataExceptionController@update');
    Route::post('/loading/schedule/disable/{sys_config}',
        '\FDT\DataLoader\Http\Controllers\SystemScheduleController@delete');
});
