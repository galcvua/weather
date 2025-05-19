<?php

declare(strict_types=1);

namespace App\Domain\Weather\Event;

use App\Domain\Weather\ValueObject\Weather;

final readonly class WeatherFetchedEvent
{
    public function __construct(
        public string $city,
        public Weather $weather,
        public string $providerName,
    ) {
    }
}
