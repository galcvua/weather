<?php

declare(strict_types=1);

namespace App\Infrastructure\Weather\Provider;

use App\Domain\Weather\Service\WeatherProviderInterface;
use App\Domain\Weather\ValueObject\Weather;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[AsDecorator(decorates: WeatherProviderInterface::class)]
final class CachedWeatherProvider implements WeatherProviderInterface
{
    public function __construct(
        private WeatherProviderInterface $decorated,
        private CacheInterface $cache,
        #[Autowire(param: 'weather.ttl')]
        private int $cacheTtl,
    ) {
    }

    public function getWeather(string $city): Weather
    {
        $cacheKey = 'weather_' . $city;

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($city): Weather {
            $item->expiresAfter($this->cacheTtl);

            return $this->decorated->getWeather($city);
        });
    }
}
