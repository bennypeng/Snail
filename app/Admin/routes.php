<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    //$router->get('/', 'HomeController@index');

    $router->resources([
        '/'                       => UserController::class,
        //'users'                 => UserController::class,
        'bags'                    => UserBagController::class,
        'snails'                  => SnailController::class,
        'configs'                 => ConfigController::class,
        'shops'                   => ShopBuffController::class,
        'dailys'                  => DailyRewardController::class,
    ]);

});
