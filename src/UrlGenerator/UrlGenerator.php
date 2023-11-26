<?php
declare(strict_types = 1);

namespace Innmind\Router\UrlGenerator;

use Innmind\Router\{
    UrlGenerator as UrlGeneratorInterface,
    Under,
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
    /** @var Sequence<Route|Under> */
    private Sequence $routes;

    /**
     * @param Sequence<Route|Under> $routes
     */
    public function __construct(Sequence $routes)
    {
        $this->routes = $routes;
    }

    public function __invoke(Name $route, Map $variables = null): Url
    {
        return $this
            ->routes
            ->flatMap(static fn($route) => match (true) {
                $route instanceof Under => $route->routes(),
                default => Sequence::of($route),
            })
            ->find(static fn(Route $candidate): bool => $candidate->is($route))
            ->map(static fn($route) => $route->template())
            ->map(static fn($template) => $template->expand($variables ?? Map::of()))
            ->match(
                static fn($route) => $route,
                static fn() => throw new NoMatchingRouteFound(),
            );
    }
}
