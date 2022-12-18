<?php
declare(strict_types = 1);

namespace Innmind\Router\UrlGenerator;

use Innmind\Router\{
    UrlGenerator as UrlGeneratorInterface,
    Route,
    Route\Name,
    Exception\NoMatchingRouteFound,
};
use Innmind\Url\Url;
use Innmind\Immutable\{
    Map,
    Set,
};

final class UrlGenerator implements UrlGeneratorInterface
{
    /** @var Set<Route> */
    private Set $routes;

    /**
     * @param Set<Route> $routes
     */
    public function __construct(Set $routes)
    {
        $this->routes = $routes;
    }

    public function __invoke(Name $route, Map $variables = null): Url
    {
        return $this
            ->routes
            ->find(static fn(Route $candidate): bool => $candidate->name()->equals($route))
            ->match(
                static fn($route) => $route,
                static fn() => throw new NoMatchingRouteFound,
            )
            ->template()
            ->expand($variables ?? Map::of());
    }
}
