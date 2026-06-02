<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class WeatherController extends Controller
{
    /**
     * GET /weather/{city}
     *
     * Always fetches fresh weather data from the external provider.
     */
    public function show(string $city): JsonResponse
    {
        // Weather provider integration is wired in the next step.
        return response()->json([
            'city' => $city,
            'source' => 'external',
        ]);
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
