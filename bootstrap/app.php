<?php

use App\Exceptions\CityNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API-only app: always render exceptions as JSON, never HTML error pages.
        $exceptions->shouldRenderJsonWhen(fn () => true);

        // "City not found" is an expected client outcome (404), not a system
        // fault — don't pollute the error log with it. Upstream failures
        // (WeatherServiceException, 502/503) are still reported.
        $exceptions->dontReport(CityNotFoundException::class);

        // Clean, consistent 404 envelope (no stack trace, even with APP_DEBUG=true).
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            return response()->json([
                'message' => 'The requested resource was not found.',
            ], 404);
        });
    })->create();
