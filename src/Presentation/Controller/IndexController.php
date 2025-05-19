<?php

declare(strict_types=1);

namespace App\Presentation\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class IndexController extends AbstractController
{
    public function __construct(
        /**
         * @var string[]
         */
        #[Autowire(param: 'weather.cities')]
        private array $cities,
    ) {
    }

    public function __invoke(): Response
    {
        $city = $this->cities[0] ?? null;

        if ($city) {
            return $this->redirectToRoute('city_' . $city);
        }

        return new Response('<html><body><h1>No cities available</h1></body></html>');
    }
}
