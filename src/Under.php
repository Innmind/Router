<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Http\{
    ServerRequest,
    Method,
    Response,
    Response\StatusCode,
};
use Innmind\UrlTemplate\Template;
use Innmind\Immutable\{
    Sequence,
    Maybe,
};

/**
 * @psalm-immutable
 */
final class Under
{
    private Template $template;
    /** @var Sequence<Route> */
    private Sequence $routes;

    /**
     * @param Sequence<Route> $routes
     */
    private function __construct(Template $template, Sequence $routes)
    {
        $this->template = $template;
        $this->routes = $routes;
    }

    /**
     * @psalm-pure
     */
    public static function of(Template $template): self
    {
        return new self($template, Sequence::of());
    }

    /**
     * @param callable(Route): Route $map
     */
    public function route(Method $method, callable $map = null): self
    {
        $map ??= static fn(Route $route): Route => $route;

        /** @psalm-suppress ImpureFunctionCall */
        return new self(
            $this->template,
            ($this->routes)($map(Route::of($method, $this->template))),
        );
    }

    public function matches(ServerRequest $request): bool
    {
        return $this->match($request)->match(
            static fn() => true,
            static fn() => false,
        );
    }

    /**
     * @return Maybe<Route>
     */
    public function match(ServerRequest $request): Maybe
    {
        return $this
            ->routes
            ->find(static fn($route) => $route->matches($request))
            ->otherwise(
                fn() => Maybe::just(Route::of($request->method(), $this->template))
                    ->filter(fn() => $this->template->matches(
                        $request
                            ->url()
                            ->withoutScheme()
                            ->withoutAuthority(),
                    ))
                    ->map(static fn($route) => $route->handle(
                        static fn($request) => Response::of(
                            StatusCode::methodNotAllowed,
                            $request->protocolVersion(),
                        ),
                    )),
            );
    }

    /**
     * @return Sequence<Route>
     */
    public function routes(): Sequence
    {
        return $this->routes;
    }
}
