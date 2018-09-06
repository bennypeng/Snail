<?php

//  简易聊天室
//Route::get('/chat', function () {
//    return view('chat/index');
//});

//  日志查询
Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

//  测试vue页面
//Route::group(['middleware' => ['cors']], function() {
//    Route::get('/web', function() {
//        return view('index');
//    });
//});


