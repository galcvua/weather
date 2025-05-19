<?php

declare(strict_types=1);

namespace App\Domain\Weather\Service;

use App\Domain\Weather\Exception\WeatherProviderException;
use App\Domain\Weather\ValueObject\Weather;

/**
 * WeatherProviderInterface is responsible for providing weather data.
 */
interface WeatherProviderInterface
{
    /**
     * Retrieves the weather data for a given city.
     *
     * @param $city the name of the city to retrieve weather data for
     *
     * @throws WeatherProviderException
     *
     * @return Weather The weather data for the specified city
     */
    public function getWeather(string $city): Weather;
}
