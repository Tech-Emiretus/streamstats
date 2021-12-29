<?php

namespace App\Providers;

use App\Services\TwitchAuthService;
use Illuminate\Support\Facades\Config;
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
        $this->app->bind(TwitchAuthService::class, function ($app) {
            return new TwitchAuthService(Config::get('twitch.client_id'), Config::get('twitch.client_secret'));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
