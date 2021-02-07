<?php
declare(strict_types = 1);

namespace Innmind\Router\UrlGenerator;

use Innmind\Router\{
    UrlGenerator as UrlGeneratorInterface,
    Route,
    Route\Name,
};
use Innmind\Url\Url;
use Innmind\Immutable\{
    Map,
    Set,
};
use function Innmind\Immutable\assertSet;

final class UrlGenerator implements UrlGeneratorInterface
{
    /** @var Set<Route> */
    private Set $routes;

    /**
     * @param Set<Route> $routes
     */
    public function __construct(Set $routes)
    {
        assertSet(Route::class, $routes, 1);

        $this->routes = $routes;
    }

    public function __invoke(Name $route, Map $variables = null): Url
    {
        /** @var Map<string, scalar|array> */
        $default = Map::of('string', 'scalar|array');

        return $this
            ->routes
            ->find(static fn(Route $candidate): bool => $candidate->name()->equals($route))
            ->template()
            ->expand($variables ?? $default);
    }
}
