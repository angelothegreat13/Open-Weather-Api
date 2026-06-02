<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTO\WeatherData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shapes the public weather response. Both endpoints render through this class
 * so they return an identical contract; only the "source" differs.
 *
 * @property-read WeatherData $resource
 */
class WeatherResource extends JsonResource
{
    /**
     * No top-level "data" wrapper — the exam expects a flat object.
     */
    public static $wrap = null;

    public function __construct(WeatherData $resource, private readonly string $source)
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'city' => $this->resource->city,
            'temperature' => $this->resource->temperature,
            'description' => $this->resource->description,
            'timestamp' => $this->resource->observedAt->toIso8601String(),
            'source' => $this->source,
        ];
    }
}
