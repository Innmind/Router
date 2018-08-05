<?php
declare(strict_types = 1);

namespace Innmind\Router\RequestMatcher;

use Innmind\Router\{
    RequestMatcher as RequestMatcherInterface,
    Route,
    Exception\NoMatchingRouteFound,
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\SetInterface;

final class RequestMatcher implements RequestMatcherInterface
{
    private $routes;

    public function __construct(SetInterface $routes)
    {
        if ((string) $routes->type() !== Route::class) {
            throw new \TypeError(sprintf(
                'Argument 1 must be of type SetInterface<%s>',
                Route::class
            ));
        }

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
