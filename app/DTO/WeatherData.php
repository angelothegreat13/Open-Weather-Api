<?php

declare(strict_types=1);

namespace App\DTO;

use Carbon\CarbonImmutable;

/**
 * Immutable representation of a single weather reading, decoupled from the
 * OpenWeatherMap response shape. Temperature is expressed in the unit the
 * provider was configured with (Celsius when units=metric).
 */
final readonly class WeatherData
{
    public function __construct(
        public string $city,
        public float $temperature,
        public string $description,
        public CarbonImmutable $observedAt,
    ) {}

    /**
     * Build from a (validated) OpenWeatherMap "current weather" payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function fromOpenWeatherMap(array $payload): self
    {
        return new self(
            city: $payload['name'],
            temperature: (float) $payload['main']['temp'],
            description: $payload['weather'][0]['description'],
            observedAt: CarbonImmutable::createFromTimestamp($payload['dt'], 'UTC'),
        );
    }
}
