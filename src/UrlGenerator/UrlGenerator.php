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
    Sequence,
};

final class UrlGenerator implements UrlGeneratorInterface
{
    /** @var Sequence<Route> */
    private Sequence $routes;

    /**
     * @param Sequence<Route> $routes
     */
    public function __construct(Sequence $routes)
    {
        $this->routes = $routes;
    }

    public function __invoke(Name $route, Map $variables = null): Url
    {
        return $this
            ->routes
            ->find(static fn(Route $candidate): bool => $candidate->name()->equals($route))
            ->map(static fn($route) => $route->template())
            ->map(static fn($template) => $template->expand($variables ?? Map::of()))
            ->match(
                static fn($route) => $route,
                static fn() => throw new NoMatchingRouteFound(),
            );
    }
}
