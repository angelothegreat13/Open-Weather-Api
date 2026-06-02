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

    /**
     * Primitive representation for caching — store-agnostic and decoupled from
     * this class's PHP definition (unlike serializing the object itself).
     *
     * @return array{city: string, temperature: float, description: string, observed_at: int}
     */
    public function toArray(): array
    {
        return [
            'city' => $this->city,
            'temperature' => $this->temperature,
            'description' => $this->description,
            'observed_at' => $this->observedAt->getTimestamp(),
        ];
    }

    /**
     * Rebuild from the primitive cache representation.
     *
     * @param  array{city: string, temperature: float, description: string, observed_at: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            city: $data['city'],
            temperature: (float) $data['temperature'],
            description: $data['description'],
            observedAt: CarbonImmutable::createFromTimestamp($data['observed_at'], 'UTC'),
        );
    }
}
