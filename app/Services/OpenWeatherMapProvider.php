<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\WeatherProvider;
use App\DTO\WeatherData;
use App\Exceptions\CityNotFoundException;
use App\Exceptions\WeatherServiceException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

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
                ->retry(2, 200, $this->shouldRetry(...), throw: false)
                ->get('weather', [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => $this->units,
                ]);
        } catch (ConnectionException $e) {
            throw WeatherServiceException::unavailable($e);
        }

        if ($response->status() === 404) {
            throw CityNotFoundException::for($city);
        }

        if ($response->failed()) {
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

    private function hasExpectedShape(mixed $payload): bool
    {
        return is_array($payload)
            && isset($payload['name'], $payload['main']['temp'], $payload['weather'][0]['description'], $payload['dt']);
    }

    /** Retry only transient failures — connection errors and upstream 5xx, never 4xx. */
    private function shouldRetry(Throwable $e): bool
    {
        return $e instanceof ConnectionException
            || ($e instanceof RequestException && (bool) $e->response?->serverError());
    }
}
