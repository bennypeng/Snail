<?php

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

//  前端资源资源下载
Route::get('res/{dir1}/{dir2}/{file}', function (){

    $arg1 = Route::input('dir1');

    $arg2 = Route::input('dir2');

    $arg3 = Route::input('file');

    $path = storage_path("res/{$arg1}/{$arg2}/{$arg3}");

    if (file_exists($path)) {

        return response()->download($path, $arg3);

    }

});

// 视频id
Route::get('vedio', function() {
    $vedioArr = ['adunit-05bf39c30f834b2e', 'adunit-170066a5c689cf72'];
    $idx = array_rand($vedioArr);
    return response($vedioArr[$idx]);
});

//  测试vue页面
//Route::group(['middleware' => ['cors']], function() {
//    Route::get('/web', function() {
//        return view('index');
//    });
//});


