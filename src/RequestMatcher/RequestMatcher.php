<?php
declare(strict_types = 1);

namespace Innmind\Router\RequestMatcher;

use Innmind\Router\{
    RequestMatcher as RequestMatcherInterface,
    Route,
};
use Innmind\Http\Message\ServerRequest;
use Innmind\Immutable\{
    Sequence,
    Maybe,
};

final class RequestMatcher implements RequestMatcherInterface
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

    public function __invoke(ServerRequest $request): Maybe
    {
        return $this->routes->find(static fn(Route $route): bool => $route->matches($request));
    }
}
