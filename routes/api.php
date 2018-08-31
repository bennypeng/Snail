<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('login', 'WxUserController@login');

//  错误返回
Route::fallback(function (){
    return response()->json(['message' => 'Not Found!', 'code' => 404], 404);
});