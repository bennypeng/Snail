<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\HelperService;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //使用singleton绑定单例
        $this->app->singleton('helper',function(){
            return new HelperService();
        });

        //使用bind绑定实例到接口以便依赖注入
        $this->app->bind('App\Contracts\HelperContract',function(){
            return new HelperService();
        });
    }
}
