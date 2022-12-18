<?php
declare(strict_types = 1);

namespace Innmind\Router\RequestMatcher;

use Innmind\Router\{
    RequestMatcher as RequestMatcherInterface,
    Route,
    Exception\NoMatchingRouteFound,
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\Set;

final class RequestMatcher implements RequestMatcherInterface
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

    public function __invoke(ServerRequest $request): Route
    {
        return $this
            ->routes
            ->find(static fn(Route $route): bool => $route->matches($request))
            ->match(
                static fn($route) => $route,
                static fn() => throw new NoMatchingRouteFound,
            );
    }
}
