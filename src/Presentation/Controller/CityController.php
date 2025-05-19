<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

class CityController extends AbstractController
{
    public function __construct(
        #[Autowire(param: 'weather.ttl')]
        private int $weatherTtl,
    ) {
    }

    public function __invoke(string $city): Response
    {
        return $this->render('city.html.twig', [
            'city' => $city,
            'updateInterval' => $this->weatherTtl * 1000 / 2, // in milliseconds
        ]);
    }
}
