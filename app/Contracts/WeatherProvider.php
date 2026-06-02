<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTO\WeatherData;
use App\Exceptions\CityNotFoundException;
use App\Exceptions\WeatherServiceException;

interface WeatherProvider
{
    /**
     * @throws CityNotFoundException
     * @throws WeatherServiceException
     */
    public function fetch(string $city): WeatherData;
}
