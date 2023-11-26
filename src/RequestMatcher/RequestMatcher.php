<?php
declare(strict_types = 1);

namespace Innmind\Router\RequestMatcher;

use Innmind\Router\{
    RequestMatcher as RequestMatcherInterface,
    Route,
    Under,
};
use Innmind\Http\ServerRequest;
use Innmind\Immutable\{
    Sequence,
    Maybe,
};

final class RequestMatcher implements RequestMatcherInterface
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

    public function __invoke(ServerRequest $request): Maybe
    {
        return $this
            ->routes
            ->find(static fn($route): bool => $route->matches($request))
            ->flatMap(static fn($route) => match (true) {
                $route instanceof Under => $route->match($request),
                default => Maybe::just($route),
            });
    }
}
