<?php

declare(strict_types=1);

namespace App\Infrastructure\RouteLoader;

use App\Presentation\Controller\CityController;
use App\Presentation\Controller\WeatherController;
use RuntimeException;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\String\Slugger\SluggerInterface;

class CitiesRouteLoader extends Loader
{
    private bool $loaded = false;

    public function __construct(
        private SluggerInterface $slugger,
        /**
         * @var string[]
         */
        #[Autowire(param: 'weather.cities')]
        private array $cities,
    ) {
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        if ($this->loaded) {
            throw new RuntimeException('Do not add this loader twice');
        }

        $routes = new RouteCollection();

        foreach ($this->cities as $city) {
            $slug = $this->slugger->slug(mb_strtolower($city));

            $defaults = ['_controller' => CityController::class, 'city' => $city];
            $route = new Route('/weather/' . $slug, $defaults);
            $routes->add('city_' . $city, $route);

            $defaults = ['_controller' => WeatherController::class, 'city' => $city];
            $route = new Route('/weather-frame/' . $slug, $defaults);
            $routes->add('weather_' . $city, $route);
        }

        $this->loaded = true;

        return $routes;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return $type === 'weather';
    }
}
