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
use function Innmind\Immutable\assertSet;

final class RequestMatcher implements RequestMatcherInterface
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

    public function __invoke(ServerRequest $request): Route
    {
        $route = $this->routes->reduce(
            null,
            static function(?Route $matched, Route $route) use ($request): ?Route {
                if ($matched instanceof Route) {
                    return $matched;
                }

                return $route->matches($request) ? $route : null;
            }
        );

        if (!$route instanceof Route) {
            throw new NoMatchingRouteFound;
        }

        return $route;
    }
}
