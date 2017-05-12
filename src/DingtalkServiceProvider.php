<?php

namespace Goodwong\LaravelDingtalk;

use Illuminate\Support\ServiceProvider;

class DingtalkServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->publishes([
            __DIR__.'/config/dingtalk.php' => config_path('dingtalk.php'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/dingtalk.php', 'dingtalk'
        );
    }
}
