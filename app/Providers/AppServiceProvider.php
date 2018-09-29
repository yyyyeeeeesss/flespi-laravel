<?php

namespace App\Providers;

use App\Services\MqttService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

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
        $this->app->bind(MqttService::class, function($app) {
            return new MqttService(
                Config::get('mqtt.server'),
                Config::get('mqtt.port'),
                Config::get('mqtt.token'));
        });
    }
}
