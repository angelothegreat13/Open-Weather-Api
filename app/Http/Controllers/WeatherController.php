<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\WeatherProvider;
use App\DTO\WeatherData;
use App\Http\Resources\WeatherResource;
use Illuminate\Support\Facades\Cache;

class WeatherController extends Controller
{
    private const CACHE_TTL_MINUTES = 10;

    public function __construct(
        private readonly WeatherProvider $weather,
    ) {}

    public function show(string $city): WeatherResource
    {
        return new WeatherResource($this->weather->fetch($city), source: 'external');
    }

    public function cached(string $city): WeatherResource
    {
        $key = $this->cacheKey($city);
        $cached = Cache::get($key);

        if (is_array($cached)) {
            return new WeatherResource(WeatherData::fromArray($cached), source: 'cache');
        }

        // Cache the facts only; "source" reflects hit vs. miss per request.
        $weather = $this->weather->fetch($city);
        Cache::put($key, $weather->toArray(), now()->addMinutes(self::CACHE_TTL_MINUTES));

        return new WeatherResource($weather, source: 'external');
    }

    private function cacheKey(string $city): string
    {
        return 'weather:'.strtolower(trim($city));
    }
}
