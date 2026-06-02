<?php

declare(strict_types=1);

namespace App\Exceptions;

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
