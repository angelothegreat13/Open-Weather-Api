<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTO\WeatherData;
use App\Exceptions\CityNotFoundException;
use App\Exceptions\WeatherServiceException;

/**
 * Abstraction over an external weather source. The controller depends on this
 * contract, not a concrete client, so the implementation is swappable and the
 * provider can be faked in tests.
 */
interface WeatherProvider
{
    /**
     * Fetch the current weather for a city.
     *
     * @throws CityNotFoundException     when the city has no weather data (upstream 404)
     * @throws WeatherServiceException   when the upstream is unreachable or errors
     */
    public function fetch(string $city): WeatherData;
}
