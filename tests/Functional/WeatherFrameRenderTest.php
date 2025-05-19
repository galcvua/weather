<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Domain\Weather\Service\WeatherProviderInterface;
use App\Domain\Weather\ValueObject\Weather;
use App\Tests\Builder\WeatherBuilder;
use DateTimeImmutable;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
class WeatherFrameRenderTest extends WebTestCase
{
    public function testWeatherFrameRendersWithStubProvider(): void
    {
        $client = static::createClient();

        $weather = (new WeatherBuilder())
            ->withLocation('Kyiv', 'Ukraine')
            ->withTemperature(25.0)
            ->withCondition('Sunny', '')
            ->withHumidity(50)
            ->withWind(5.0)
            ->withLastUpdated(new DateTimeImmutable('2024-05-20 12:00:00', new DateTimeZone('UTC')))
            ->build();

        $stubProvider = new class($weather) implements WeatherProviderInterface {
            public function __construct(private Weather $weather)
            {
            }

            public function getWeather(string $city): Weather
            {
                return $this->weather;
            }
        };

        $client->getContainer()->set(WeatherProviderInterface::class, $stubProvider);

        $client->request('GET', '/weather-frame/kyiv');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('turbo-frame#weather_data');
        $this->assertSelectorTextContains('.weather_data', 'Kyiv');
        $this->assertSelectorTextContains('.weather_data', 'Sunny');
        $this->assertSelectorTextContains('.weather_data', '25');
        $this->assertSelectorTextContains('.weather_data', '50%');
        $this->assertSelectorTextContains('.weather_data', '5 km/h');
        $this->assertSelectorTextContains('.weather_data', '12:00 UTC');
    }
}
