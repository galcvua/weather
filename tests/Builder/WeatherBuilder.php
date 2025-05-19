<?php

declare(strict_types=1);

namespace App\Tests\Builder;

use App\Domain\Weather\ValueObject\Condition;
use App\Domain\Weather\ValueObject\Location;
use App\Domain\Weather\ValueObject\Weather;
use DateTimeImmutable;

class WeatherBuilder
{
    private Location $location;
    private float $temperature = 20.0;
    private Condition $condition;
    private int $humidity = 50;
    private float $wind = 5.0;
    private DateTimeImmutable $lastUpdated;

    public function __construct()
    {
        $this->location = new Location('Kyiv', 'Ukraine');
        $this->condition = new Condition('Sunny', '');
        $this->lastUpdated = new DateTimeImmutable();
    }

    public function withLocation(Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function withTemperature(float $temperature): self
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function withCondition(Condition $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    public function withHumidity(int $humidity): self
    {
        $this->humidity = $humidity;

        return $this;
    }

    public function withWind(float $wind): self
    {
        $this->wind = $wind;

        return $this;
    }

    public function withLastUpdated(DateTimeImmutable $dt): self
    {
        $this->lastUpdated = $dt;

        return $this;
    }

    public function build(): Weather
    {
        return new Weather(
            location: $this->location,
            temperature: $this->temperature,
            condition: $this->condition,
            humidity: $this->humidity,
            wind: $this->wind,
            lastUpdated: $this->lastUpdated,
        );
    }
}
