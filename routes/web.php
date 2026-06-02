<?php

use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'service' => 'Open Weather API',
    'endpoints' => [
        'GET /weather/{city}' => 'Fetch real-time weather for a city.',
        'GET /weather/{city}/cached' => 'Same data, cached for 10 minutes.',
    ],
]));

Route::get('/weather/{city}', [WeatherController::class, 'show']);
Route::get('/weather/{city}/cached', [WeatherController::class, 'cached']);
