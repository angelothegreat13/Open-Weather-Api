<?php

namespace App\Providers;

use App\Contracts\WeatherProvider;
use App\Services\OpenWeatherMapProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WeatherProvider::class, function ($app): OpenWeatherMapProvider {
            $config = $app['config']->get('services.openweather');

            return new OpenWeatherMapProvider(
                apiKey: (string) $config['key'],
                baseUrl: (string) $config['base_url'],
                units: (string) $config['units'],
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
