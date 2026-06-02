<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\WeatherProvider;
use App\DTO\WeatherData;
use App\Http\Resources\WeatherResource;
use Illuminate\Support\Facades\Cache;

class WeatherController extends Controller
{
    /**
     * How long a cached reading stays fresh, per the exam requirement.
     */
    private const CACHE_TTL_MINUTES = 10;

    public function __construct(
        private readonly WeatherProvider $weather,
    ) {}

    /**
     * GET /weather/{city}
     *
     * Always fetches fresh weather data from the external provider.
     */
    public function show(string $city): WeatherResource
    {
        return new WeatherResource($this->weather->fetch($city), source: 'external');
    }

    /**
     * GET /weather/{city}/cached
     *
     * Serves cached data when available, otherwise fetches, caches, and returns
     * it. Only the weather facts are cached — "source" is decided per request by
     * whether the entry already existed, so it correctly reads "cache" on a hit
     * and "external" on a miss.
     */
    public function cached(string $city): WeatherResource
    {
        $key = $this->cacheKey($city);

        $cached = Cache::get($key);

        if (is_array($cached)) {
            return new WeatherResource(WeatherData::fromArray($cached), source: 'cache');
        }

        $weather = $this->weather->fetch($city);
        Cache::put($key, $weather->toArray(), now()->addMinutes(self::CACHE_TTL_MINUTES));

        return new WeatherResource($weather, source: 'external');
    }

    /**
     * Normalize the city so "London", "london" and " London " share one entry.
     */
    private function cacheKey(string $city): string
    {
        return 'weather:'.strtolower(trim($city));
    }
}
