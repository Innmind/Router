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
use function Innmind\Immutable\assertSet;

final class UrlGenerator implements UrlGeneratorInterface
{
    /** @var Map<string, Template> */
    private Map $routes;

    /**
     * @param Set<Route> $routes
     */
    public function __construct(Set $routes)
    {
        assertSet(Route::class, $routes, 1);

        /** @var Map<string, Template> */
        $this->routes = $routes->toMapOf(
            'string',
            Template::class,
            static function(Route $route): \Generator {
                yield (string) $route->name() => $route->template();
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
