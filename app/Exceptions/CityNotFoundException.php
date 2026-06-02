<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * The request was valid, but the upstream service has no weather data for the
 * given city (OpenWeatherMap responded 404).
 */
class CityNotFoundException extends WeatherException
{
    public static function for(string $city): self
    {
        return new self("Weather data for '{$city}' was not found.");
    }

    public function statusCode(): int
    {
        return 404;
    }
}
