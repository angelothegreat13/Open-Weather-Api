<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

class WeatherServiceException extends WeatherException
{
    public function __construct(
        string $message,
        private readonly int $statusCode = 503,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function unavailable(?Throwable $previous = null): self
    {
        return new self('The weather service is currently unavailable. Please try again later.', 503, $previous);
    }

    public static function badResponse(?Throwable $previous = null): self
    {
        return new self('The weather service returned an unexpected response.', 502, $previous);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
