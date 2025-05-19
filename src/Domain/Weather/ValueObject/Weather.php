<?php

declare(strict_types=1);

namespace App\Domain\Weather\ValueObject;

use DateTimeImmutable;

final readonly class Weather
{
    public function __construct(
        public Location $location,
        public float $temperature,
        public Condition $condition,
        public int $humidity,
        public float $wind,
        public DateTimeImmutable $lastUpdated,
    ) {
    }
}
