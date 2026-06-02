<?php

declare(strict_types=1);

namespace App\DTO;

use Carbon\CarbonImmutable;

final readonly class WeatherData
{
    public function __construct(
        public string $city,
        public float $temperature,
        public string $description,
        public CarbonImmutable $observedAt,
    ) {}

    /** @param array<string, mixed> $payload */
    public static function fromOpenWeatherMap(array $payload): self
    {
        return new self(
            city: $payload['name'],
            temperature: (float) $payload['main']['temp'],
            description: $payload['weather'][0]['description'],
            observedAt: CarbonImmutable::createFromTimestamp($payload['dt'], 'UTC'),
        );
    }

    /** @return array{city: string, temperature: float, description: string, observed_at: int} */
    public function toArray(): array
    {
        return [
            'city' => $this->city,
            'temperature' => $this->temperature,
            'description' => $this->description,
            'observed_at' => $this->observedAt->getTimestamp(),
        ];
    }

    /** @param array{city: string, temperature: float, description: string, observed_at: int} $data */
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
