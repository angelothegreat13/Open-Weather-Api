<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
});

/** @return array<string, mixed> */
function fakeWeatherPayload(string $city = 'London'): array
{
    return [
        'name' => $city,
        'weather' => [['description' => 'broken clouds']],
        'main' => ['temp' => 16.5],
        'dt' => 1780397389,
    ];
}

it('returns current weather from the external provider', function () {
    Http::fake([
        'api.openweathermap.org/*' => Http::response(fakeWeatherPayload(), 200),
    ]);

    $this->getJson('/weather/London')
        ->assertOk()
        ->assertJson([
            'city' => 'London',
            'temperature' => 16.5,
            'description' => 'broken clouds',
            'source' => 'external',
        ])
        ->assertJsonStructure(['city', 'temperature', 'description', 'timestamp', 'source']);
});

it('serves the second cached request from cache without calling the API again', function () {
    Http::fake([
        'api.openweathermap.org/*' => Http::response(fakeWeatherPayload('Tokyo'), 200),
    ]);

    $first = $this->getJson('/weather/Tokyo/cached');
    $second = $this->getJson('/weather/Tokyo/cached');

    $first->assertOk()->assertJsonPath('source', 'external');
    $second->assertOk()->assertJsonPath('source', 'cache');

    // Same data, only "source" changes.
    expect($second->json('temperature'))->toBe($first->json('temperature'));

    Http::assertSentCount(1);
});

it('returns 404 with a clean message and does not retry when the city is not found', function () {
    Http::fake([
        'api.openweathermap.org/*' => Http::response(['cod' => '404', 'message' => 'city not found'], 404),
    ]);

    $this->getJson('/weather/nope')
        ->assertNotFound()
        ->assertExactJson(['message' => "Weather data for 'nope' was not found."]);

    Http::assertSentCount(1);
});

it('retries on an upstream 5xx then returns 502', function () {
    Http::fake([
        'api.openweathermap.org/*' => Http::response('upstream boom', 500),
    ]);

    $this->getJson('/weather/London')
        ->assertStatus(502)
        ->assertExactJson(['message' => 'The weather service returned an unexpected response.']);

    // retry(2) = 2 attempts total.
    Http::assertSentCount(2);
});
