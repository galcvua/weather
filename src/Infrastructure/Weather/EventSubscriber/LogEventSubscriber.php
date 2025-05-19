<?php

declare(strict_types=1);

namespace App\Infrastructure\Weather\EventSubscriber;

use App\Domain\Weather\Event\WeatherFetchedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class LogEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WeatherFetchedEvent::class => 'onWeatherFetched',
        ];
    }

    public function onWeatherFetched(WeatherFetchedEvent $event): void
    {
        $this->logger->info(sprintf(
            'Weather fetched for %s using %s: %s',
            $event->city,
            $event->providerName,
            json_encode($event->weather, JSON_THROW_ON_ERROR)
        ));
    }
}
