<?php

declare(strict_types=1);

namespace App\Infrastructure\Weather\Provider;

use App\Domain\Weather\Event\WeatherFetchedEvent;
use App\Domain\Weather\Exception\WeatherProviderException;
use App\Domain\Weather\Service\WeatherProviderInterface;
use App\Domain\Weather\ValueObject\Condition;
use App\Domain\Weather\ValueObject\Location;
use App\Domain\Weather\ValueObject\Weather;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use SensitiveParameter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class WeatherApiProvider implements WeatherProviderInterface
{
    public const BASE_URL = 'https://api.weatherapi.com/v1/current.json';

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly HttpClientInterface $client,
        #[Autowire(env: 'WHETHER_API_KEY')]
        #[SensitiveParameter]
        private readonly string $apiKey,
    ) {
    }

    public function getWeather(string $city): Weather
    {
        try {
            $response = $this->client->request('GET', self::BASE_URL, [
                'query' => [
                    'key' => $this->apiKey,
                    'q' => $city,
                ],
            ]);
        } catch (TransportExceptionInterface $e) {
            throw WeatherProviderException::failedToFetch(self::class, 'Transport error: ' . $e->getMessage());
        }

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw WeatherProviderException::failedToFetch(
                self::class,
                'Invalid response code: ' . $response->getStatusCode()
            );
        }

        try {
            $data = $response->toArray(false);

            $weather = new Weather(
                location: new Location($data['location']['name'], $data['location']['country']),
                temperature: (float) $data['current']['temp_c'],
                condition: new Condition($data['current']['condition']['text'], $data['current']['condition']['icon']),
                humidity: (int) $data['current']['humidity'],
                wind: (float) $data['current']['wind_kph'],
                lastUpdated: $this->createLastUpdated(
                    lastUpdated: $data['current']['last_updated'],
                    tzId: $data['location']['tz_id'] ?? 'UTC',
                    lastUpdatedEpoch: (int) $data['current']['last_updated_epoch'],
                ),
            );
        } catch (Exception $e) {
            throw WeatherProviderException::failedToFetch(
                self::class,
                'Invalid response structure: ' . $e->getMessage()
            );
        }

        $this->eventDispatcher->dispatch(new WeatherFetchedEvent($city, $weather, self::class));

        return $weather;
    }

    /**
     * In case of invalid timezone, fallback to UTC with epoch timestamp.
     */
    private function createLastUpdated(
        string $lastUpdated,
        string $tzId,
        int $lastUpdatedEpoch,
    ): DateTimeImmutable {
        try {
            return new DateTimeImmutable($lastUpdated, new DateTimeZone($tzId));
        } catch (Exception) {
            // ignore and fallback
        }

        // fallback: UTC з epoch
        return new DateTimeImmutable('@' . $lastUpdatedEpoch);
    }
}
