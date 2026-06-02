# Open Weather API

A small Laravel service that returns the current weather for a city using the
OpenWeatherMap API, with a second endpoint that caches the result.

Built on Laravel 13.

## Requirements

- PHP 8.3+
- Composer
- An OpenWeatherMap API key (the free tier works fine)

## Running it

```bash
composer install
cp .env.example .env
```

Open `.env` and drop your key in:

```
OPENWEATHER_API_KEY=your_key_here
```

Then start the server:

```bash
php artisan serve
```

`.env.example` already has an `APP_KEY` set, so you don't need to run
`key:generate`. Caching uses the file driver by default, so there's nothing
else to configure.

## Endpoints

Both endpoints return the same JSON. The only thing that changes is `source`.

`GET /weather/{city}` fetches live data on every call:

```json
{
  "city": "London",
  "temperature": 16.33,
  "description": "light rain",
  "timestamp": "2026-06-02T10:27:45+00:00",
  "source": "external"
}
```

`GET /weather/{city}/cached` returns the same data but caches it for 10 minutes.
The first call comes back with `"source": "external"`; any call within the
10-minute window returns `"source": "cache"`.

Temperature is in Celsius (the request sends `units=metric`). If a city isn't
found you get a 404, and if OpenWeatherMap is unreachable or returns an error
you get a 502 or 503. Every error is returned as JSON.

## Tests

```bash
php artisan test
```

The tests use Laravel's HTTP fake, so they never hit the real API. They cover
the happy path, the caching behavior (including a check that the API is only
called once across two cached requests), a city-not-found case, and an upstream
failure.

## How it's put together

I tried to keep the layers thin and only add structure where it earns its place.

- The controller just orchestrates. It has no idea about HTTP or OpenWeatherMap.
- The external API logic sits behind a `WeatherProvider` interface, with
  `OpenWeatherMapProvider` as the concrete implementation. The controller
  depends on the interface, which keeps things swappable and makes the provider
  easy to fake in tests.
- A `WeatherData` DTO moves the data around internally, and a `WeatherResource`
  shapes the JSON that goes out. The provider is the only place that ever touches
  OpenWeatherMap's raw response shape, so if their payload changes there's just
  one spot to update.

A few decisions worth pointing out:

- For the cached endpoint I used `Cache::get` and `Cache::put` instead of
  `Cache::remember`. `remember()` hides whether the value was a hit or a miss,
  and I needed that to decide the `source`. The cache key is normalized
  (lowercased and trimmed) so "London" and " london " share one entry, and only
  the weather facts get cached, never the source flag.
- Upstream failures are turned into typed exceptions at the provider and mapped
  to sensible status codes. The underlying cause is logged but never sent back to
  the client. Retries only happen on connection errors and 5xx responses, not on
  a 404, since there's no point retrying a city that doesn't exist.
