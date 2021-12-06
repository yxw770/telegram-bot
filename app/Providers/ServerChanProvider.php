<?php

namespace App\Providers;

use App\Services\ServerChanService;
use Illuminate\Support\ServiceProvider;

class ServerChanProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // 将服务接口注绑定至服务容器
        $this->app->bind('App\Contracts\ServerChanContract', function(){
            return new ServerChanService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
