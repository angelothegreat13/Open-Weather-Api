<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\WeatherProvider;
use App\Http\Resources\WeatherResource;
use Illuminate\Http\JsonResponse;

class WeatherController extends Controller
{
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
     * Serves cached data when available, otherwise fetches and caches it.
     */
    public function cached(string $city): JsonResponse
    {
        // Cache + provider integration is wired in the next step.
        return response()->json([
            'city' => $city,
            'source' => 'cache',
        ]);
    }
}
