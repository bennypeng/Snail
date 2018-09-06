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

//  需要带上sessionId
Route::group(['middleware' => 'skey'], function() {

    //  查看每日奖励
    Route::get('conf/daily', 'ConfigController@getDailyConf');

    //  领取每日奖励
    Route::get('conf/daily/{day}', 'ConfigController@getDailyAward')->where('day', '[1-7]');

    //  查看商店配置
    Route::get('conf/shop', 'ConfigController@getBuffShopConf');

    //  购买商品
    Route::get('conf/shop/{goodId}', 'ConfigController@buyBuff');


});

//  登录授权
Route::post('user/login', 'WxUserController@login');

//  错误返回
Route::fallback(function (){
    return response()->json(['message' => 'Not Found!', 'code' => 404], 404);
});