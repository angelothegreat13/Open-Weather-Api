<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\WeatherProvider;
use App\DTO\WeatherData;
use App\Exceptions\CityNotFoundException;
use App\Exceptions\WeatherServiceException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * OpenWeatherMap implementation of the WeatherProvider contract.
 *
 * All knowledge of how OpenWeatherMap behaves — its query params, status codes
 * and response shape — is isolated here. Upstream failures are translated into
 * domain exceptions at this boundary so the rest of the app never sees raw
 * HTTP details.
 */
final readonly class OpenWeatherMapProvider implements WeatherProvider
{
    public function __construct(
        private string $apiKey,
        private string $baseUrl,
        private string $units,
    ) {}

    public function fetch(string $city): WeatherData
    {
        try {
            $response = Http::baseUrl($this->baseUrl)
                ->timeout(5)
                ->retry(2, 200, throw: false) // retry transient failures only
                ->get('weather', [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => $this->units,
                ]);
        } catch (ConnectionException $e) {
            // Timeout / DNS / connection refused — never produced a response.
            throw WeatherServiceException::unavailable($e);
        }

        if ($response->status() === 404) {
            throw CityNotFoundException::for($city);
        }

        if ($response->failed()) {
            // 5xx, 401 (bad key), 429, etc. Keep the cause for the log only.
            throw WeatherServiceException::badResponse(
                new RuntimeException("OpenWeatherMap responded {$response->status()}: {$response->body()}")
            );
        }

        $payload = $response->json();

        if (! $this->hasExpectedShape($payload)) {
            throw WeatherServiceException::badResponse(
                new RuntimeException('OpenWeatherMap returned an unexpected payload: '.$response->body())
            );
        }

        return WeatherData::fromOpenWeatherMap($payload);
    }

    /**
     * Guard against a 200 response whose body is missing fields we rely on.
     *
     * @param  mixed  $payload
     */
    private function hasExpectedShape($payload): bool
    {
        return is_array($payload)
            && isset($payload['name'], $payload['main']['temp'], $payload['weather'][0]['description'], $payload['dt']);
    }
}
