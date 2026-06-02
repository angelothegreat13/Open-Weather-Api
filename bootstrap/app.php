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
        // API-only: respond with JSON, and don't log expected 404s.
        $exceptions->shouldRenderJsonWhen(fn () => true);
        $exceptions->dontReport(CityNotFoundException::class);

        $exceptions->render(fn (NotFoundHttpException $e, Request $request) => response()->json([
            'message' => 'The requested resource was not found.',
        ], 404));
    })->create();
