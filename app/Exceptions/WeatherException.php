<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Base for all weather-domain failures.
 *
 * Each subclass declares the HTTP status it maps to; the JSON envelope is
 * rendered here once so every weather error returns an identical shape.
 * Laravel automatically invokes render() when the exception bubbles up.
 */
abstract class WeatherException extends Exception
{
    /**
     * HTTP status code this failure maps to.
     */
    abstract public function statusCode(): int;

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], $this->statusCode());
    }
}
