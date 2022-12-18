<?php
declare(strict_types = 1);

namespace Innmind\Router\RequestMatcher;

use Innmind\Router\{
    RequestMatcher as RequestMatcherInterface,
    Route,
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\{
    Set,
    Maybe,
};

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

    public function __invoke(ServerRequest $request): Maybe
    {
        return $this->routes->find(static fn(Route $route): bool => $route->matches($request));
    }
}
