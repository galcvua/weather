<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Weather\Provider;

use App\Domain\Weather\Service\WeatherProviderInterface;
use App\Infrastructure\Weather\Provider\CachedWeatherProvider;
use App\Tests\Builder\WeatherBuilder;
use Closure;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @internal
 *
 * @coversDefaultClass \App\Infrastructure\Weather\Provider\CachedWeatherProvider
 */
class CachedWeatherProviderTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getWeather
     */
    public function testGetWeatherReturnsCachedValue(): void
    {
        $city = 'Kyiv';

        $weather = (new WeatherBuilder())->build();

        $decorated = $this->createMock(WeatherProviderInterface::class);
        $decorated->expects($this->never())->method('getWeather');

        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())
            ->method('get')
            ->with('weather_Kyiv', $this->isInstanceOf(Closure::class))
            ->willReturn($weather);

        $provider = new CachedWeatherProvider($decorated, $cache, 600);

        $result = $provider->getWeather($city);

        $this->assertSame($weather, $result);
    }

    /**
     * @covers ::__construct
     * @covers ::getWeather
     */
    public function testGetWeatherFetchesAndCachesIfNotCached(): void
    {
        $city = 'Lviv';
        $weather = (new WeatherBuilder())->build();
        $decorated = $this->createMock(WeatherProviderInterface::class);
        $decorated->expects($this->once())
            ->method('getWeather')
            ->with($city)
            ->willReturn($weather);

        $cacheItem = $this->createMock(ItemInterface::class);
        $cacheItem->expects($this->once())->method('expiresAfter')->with(300);

        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->once())
            ->method('get')
            ->with('weather_Lviv', $this->isInstanceOf(Closure::class))
            ->willReturnCallback(function ($key, $callback) use ($cacheItem) {
                return $callback($cacheItem);
            });

        $provider = new CachedWeatherProvider($decorated, $cache, 300);

        $result = $provider->getWeather($city);

        $this->assertSame($weather, $result);
    }
}
