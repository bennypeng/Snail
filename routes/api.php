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
    Route::post('conf/daily/{day}', 'ConfigController@getDailyAward')
        ->where('day', '[1-7]');

    //  查看商店配置
    Route::get('conf/shop', 'ConfigController@getBuffShopConf');

    //  购买商品
    Route::post('conf/shop/{goodId}', 'ConfigController@buyBuff')
        ->where('goodId', '[1-6]');

    //  蜗牛购买列表
    Route::get('snail/list', 'SnailController@list');

    //  合成蜗牛
    Route::post('snail/level', 'SnailController@combine');

    //  购买蜗牛
    Route::post('snail/buy', 'SnailController@buy');

    //  蜗牛上/下阵
    Route::post('snail/join', 'SnailController@join');

    //  回收蜗牛
    Route::post('snail/recly', 'SnailController@recly');

    //  集装箱增加蜗牛
    Route::post('snail/pack', 'SnailController@pack');

    //  分享领取双倍收益
    Route::get('conf/double', 'ConfigController@double');
});

//  登录授权
Route::post('user/login', 'WxUserController@login');

//  错误返回
Route::fallback(function (){
    return response()->json(['message' => 'Not Found!', 'code' => 404], 404);
});