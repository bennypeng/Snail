<?php

//  简易聊天室
//Route::get('/chat', function () {
//    return view('chat/index');
//});

//  日志查询
Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

//  证书验证
Route::get('.well-known/acme-challenge/{file}', function () {

    $arg1 = Route::input('file');

    if (file_exists(storage_path($arg1))) {
        $content = file_get_contents(storage_path($arg1));

        $response = Response::make($content);

        $response->headers->set('Content-type','text/plain');

        return $response;
    }
});

//  测试vue页面
//Route::group(['middleware' => ['cors']], function() {
//    Route::get('/web', function() {
//        return view('index');
//    });
//});


