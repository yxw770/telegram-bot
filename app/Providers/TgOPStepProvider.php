<?php

namespace App\Providers;

use App\Services\TgOPStepService;
use Illuminate\Support\ServiceProvider;

/***
 * telegram 操作步骤服务提供者
 * Author:  Godfrey
 * Date:    2021-12-03
 * Time:    12:14
 * Email:   yxw770@gmail.com
 * Class TgOPStepProvider
 * @package App\Providers
 */
class TgOPStepProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // 将服务接口注绑定至服务容器
        $this->app->bind('App\Contracts\TgOPStepContract', function(){
            return new TgOPStepService();
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
