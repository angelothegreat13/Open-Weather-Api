<?php

declare(strict_types=1);

use App\DTO\WeatherData;
use Carbon\CarbonImmutable;

it('builds from an OpenWeatherMap payload', function () {
    $dto = WeatherData::fromOpenWeatherMap([
        'name' => 'London',
        'weather' => [['description' => 'light rain']],
        'main' => ['temp' => 16.33],
        'dt' => 1780397389,
    ]);

    expect($dto->city)->toBe('London')
        ->and($dto->temperature)->toBe(16.33)
        ->and($dto->description)->toBe('light rain')
        ->and($dto->observedAt)->toBeInstanceOf(CarbonImmutable::class)
        ->and($dto->observedAt->getTimestamp())->toBe(1780397389);
});

it('round-trips through its cache array form', function () {
    $original = new WeatherData(
        city: 'Tokyo',
        temperature: 21.5,
        description: 'broken clouds',
        observedAt: CarbonImmutable::createFromTimestamp(1780397389, 'UTC'),
    );

    $restored = WeatherData::fromArray($original->toArray());

    expect($restored->toArray())->toBe($original->toArray())
        ->and($restored->city)->toBe('Tokyo')
        ->and($restored->temperature)->toBe(21.5)
        ->and($restored->description)->toBe('broken clouds')
        ->and($restored->observedAt->getTimestamp())->toBe(1780397389);
});
