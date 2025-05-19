<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Domain\Weather\Service\WeatherProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class WeatherController extends AbstractController
{
    public function __invoke(string $city, WeatherProviderInterface $provider): Response
    {
        $weather = $provider->getWeather($city);

        return $this->render('_weather.html.twig', [
            'city' => $city,
            'weather' => $weather,
        ]);
    }
}
