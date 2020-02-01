<?php
declare(strict_types = 1);

namespace Innmind\Router\UrlGenerator;

use Innmind\Router\{
    UrlGenerator as UrlGeneratorInterface,
    Route,
    Route\Name,
};
use Innmind\Url\UrlInterface;
use Innmind\UrlTemplate\Template;
use Innmind\Immutable\{
    MapInterface,
    Map,
    SetInterface,
};

final class UrlGenerator implements UrlGeneratorInterface
{
    private Map $routes;

    public function __construct(SetInterface $routes)
    {
        if ((string) $routes->type() !== Route::class) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type SetInterface<%s>',
                Route::class
            ));
        }

        $this->routes = $routes->reduce(
            new Map('string', Template::class),
            static function(MapInterface $routes, Route $route): MapInterface {
                return $routes->put(
                    (string) $route->name(),
                    $route->template()
                );
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Name $route, MapInterface $variables = null): UrlInterface
    {
        return $this
            ->routes
            ->get((string) $route)
            ->expand($variables ?? new Map('string', 'variable'));
    }
}
