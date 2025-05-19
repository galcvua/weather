<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Weather\Provider;

use App\Domain\Weather\Event\WeatherFetchedEvent;
use App\Domain\Weather\Exception\WeatherProviderException;
use App\Domain\Weather\ValueObject\Weather;
use App\Infrastructure\Weather\Provider\WeatherApiProvider;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @internal
 *
 * @covers \App\Infrastructure\Weather\Provider\WeatherApiProvider
 */
class WeatherApiProviderTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getWeather
     */
    public function testGetWeatherReturnsWeatherObject(): void
    {
        $city = 'Kyiv';
        $apiKey = 'test-key';

        $mockResponseData = [
            'location' => [
                'name' => 'Kyiv',
                'country' => 'Ukraine',
            ],
            'current' => [
                'temp_c' => 20.5,
                'condition' => [
                    'text' => 'Sunny',
                    'icon' => '//cdn.weatherapi.com/weather/64x64/day/113.png',
                ],
                'humidity' => 60,
                'wind_kph' => 10.0,
                'last_updated' => '2024-05-18 12:00',
                'last_updated_epoch' => 1716038400,
            ],
        ];

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $mockResponse->method('toArray')->willReturn($mockResponseData);

        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient->method('request')->willReturn($mockResponse);

        $mockDispatcher = $this->createMock(EventDispatcherInterface::class);
        $mockDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(WeatherFetchedEvent::class));

        $provider = new WeatherApiProvider(
            $mockDispatcher,
            $mockHttpClient,
            $apiKey
        );

        $weather = $provider->getWeather($city);

        $this->assertInstanceOf(Weather::class, $weather);
        $this->assertEquals('Kyiv', $weather->location->city);
        $this->assertEquals('Ukraine', $weather->location->country);
        $this->assertEquals(20.5, $weather->temperature);
        $this->assertEquals('Sunny', $weather->condition->label);
        $this->assertEquals(60, $weather->humidity);
        $this->assertEquals(10.0, $weather->wind);
        $this->assertEquals(
            (new DateTimeImmutable('2024-05-18 12:00'))->format('Y-m-d H:i'),
            $weather->lastUpdated->format('Y-m-d H:i')
        );
    }

    public function testInvalidTimezone(): void
    {
        $city = 'Kyiv';
        $apiKey = 'test-key';

        $mockResponseData = [
            'location' => [
                'name' => 'Kyiv',
                'country' => 'Ukraine',
                'tz_id' => 'Invalid/Timezone',
            ],
            'current' => [
                'temp_c' => 20.5,
                'condition' => [
                    'text' => 'Sunny',
                    'icon' => '//cdn.weatherapi.com/weather/64x64/day/113.png',
                ],
                'humidity' => 60,
                'wind_kph' => 10.0,
                'last_updated' => '2024-05-18 12:00',
                'last_updated_epoch' => 1747665900,
            ],
        ];

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $mockResponse->method('toArray')->willReturn($mockResponseData);

        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient->method('request')->willReturn($mockResponse);

        $mockDispatcher = $this->createMock(EventDispatcherInterface::class);
        $mockDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(WeatherFetchedEvent::class));

        $provider = new WeatherApiProvider(
            $mockDispatcher,
            $mockHttpClient,
            $apiKey
        );

        $weather = $provider->getWeather($city);

        $this->assertInstanceOf(Weather::class, $weather);
        $this->assertSame(1747665900, $weather->lastUpdated->getTimestamp());
    }

    /**
     * @covers ::__construct
     * @covers ::getWeather
     */
    public function testGetWeatherThrowsOnTransportError(): void
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient->method('request')->willThrowException(
            $this->createMock(TransportExceptionInterface::class)
        );

        $mockDispatcher = $this->createMock(EventDispatcherInterface::class);

        $provider = new WeatherApiProvider(
            $mockDispatcher,
            $mockHttpClient,
            'test-key'
        );

        $this->expectException(WeatherProviderException::class);
        $provider->getWeather('Kyiv');
    }

    /**
     * @covers ::__construct
     * @covers ::getWeather
     */
    public function testGetWeatherThrowsOnInvalidResponse(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(Response::HTTP_BAD_REQUEST);

        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient->method('request')->willReturn($mockResponse);

        $mockDispatcher = $this->createMock(EventDispatcherInterface::class);

        $provider = new WeatherApiProvider(
            $mockDispatcher,
            $mockHttpClient,
            'test-key'
        );

        $this->expectException(WeatherProviderException::class);
        $provider->getWeather('Kyiv');
    }

    /**
     * @covers ::__construct
     * @covers ::getWeather
     */
    public function testGetWeatherThrowsOnInvalidResponseStructure(): void
    {
        $mockResponseData = [
            'location' => [
                'name' => 'Kyiv',
                'country' => 'Ukraine',
            ],
            'current' => [
                'temp_c' => 20.5,
                // Missing condition, humidity, wind_kph, last_updated
            ],
        ];

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $mockResponse->method('toArray')->willReturn($mockResponseData);

        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockHttpClient->method('request')->willReturn($mockResponse);

        $mockDispatcher = $this->createMock(EventDispatcherInterface::class);

        $provider = new WeatherApiProvider(
            $mockDispatcher,
            $mockHttpClient,
            'test-key'
        );

        $this->expectException(WeatherProviderException::class);
        $provider->getWeather('Kyiv');
    }
}
