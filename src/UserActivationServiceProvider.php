<?php

namespace Fen9li\LaravelUserActivation;

use Illuminate\Support\ServiceProvider;

class UserActivationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->loadMigrationsFrom(__DIR__.'/Migrations');
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadViewsFrom(__DIR__.'/Views','laravel-user-activation');

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->make('Fen9li\LaravelUserActivation\ActivateController');
        $this->mergeConfigFrom(__DIR__.'/Config/voicelib.php', 'voicelib' );
    }
}
