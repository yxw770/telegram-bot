<?php

namespace App\Providers;

use App\Services\UserOPService;
use Illuminate\Support\ServiceProvider;

class UserOPProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->bind('App\Contracts\UserOPContract', function(){
            return new UserOPService();
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
