<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

/**
 * The upstream weather service failed in a way that is not the client's fault:
 * a timeout/connection error, a 5xx, a rejected API key, or an unparseable body.
 *
 * The original cause should be passed as $previous so it is logged (via report())
 * for debugging, while the client only ever sees the clean message below.
 */
class WeatherServiceException extends WeatherException
{
    public function __construct(
        string $message,
        private readonly int $statusCode = 503,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Upstream is unreachable or timed out — transient, the client may retry.
     */
    public static function unavailable(?Throwable $previous = null): self
    {
        return new self('The weather service is currently unavailable. Please try again later.', 503, $previous);
    }

    /**
     * Upstream returned a response we cannot honor (5xx, bad key, malformed body).
     */
    public static function badResponse(?Throwable $previous = null): self
    {
        return new self('The weather service returned an unexpected response.', 502, $previous);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
