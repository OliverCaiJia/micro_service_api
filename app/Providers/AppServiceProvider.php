<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // UCenter SSO登录注销
        $this->app->bind(
            \MyController\UCClient\Contracts\UCenterSSOContract::class,
            \App\UCenter\MyUCenterSSO::class
        );
        // 避免开启了 barryvdh/laravel-debugbar 插件后影响 UCenterAPI 的输出结果
        $this->app->bind(
            \MyController\UCClient\Contracts\UCenterAPIExecuteFilterContract::class, 
            \App\UCenter\MyUCenterAPIExecuteFilter::class
        );
        
    }

}
