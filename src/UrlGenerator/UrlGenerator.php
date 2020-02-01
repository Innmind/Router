<?php
declare(strict_types = 1);

namespace Innmind\Router\UrlGenerator;

use Innmind\Router\{
    UrlGenerator as UrlGeneratorInterface,
    Route,
    Route\Name,
};
use Innmind\Url\Url;
use Innmind\UrlTemplate\Template;
use Innmind\Immutable\{
    Map,
    Set,
};

final class UrlGenerator implements UrlGeneratorInterface
{
    /** @var Map<string, Template> */
    private Map $routes;

    /**
     * @param Set<Route> $routes
     */
    public function __construct(Set $routes)
    {
        if ((string) $routes->type() !== Route::class) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type Set<%s>',
                Route::class,
            ));
        }

        /** @var Map<string, Template> */
        $this->routes = $routes->reduce(
            Map::of('string', Template::class),
            static function(Map $routes, Route $route): Map {
                return ($routes)(
                    (string) $route->name(),
                    $route->template(),
                );
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Name $route, Map $variables = null): Url
    {
        /** @var Map<string, scalar|array> */
        $default = Map::of('string', 'scalar|array');

        return $this
            ->routes
            ->get((string) $route)
            ->expand($variables ?? $default);
    }
}
