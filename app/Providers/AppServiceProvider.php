<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Worksome\Envy\EnvyServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('development')) {
            $this->app->register(EnvyServiceProvider::class);
        }
    }
}
