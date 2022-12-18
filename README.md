# Router

[![codecov](https://codecov.io/gh/Innmind/Router/branch/develop/graph/badge.svg?branch=master)](https://codecov.io/gh/Innmind/Router)
[![Build Status](https://github.com/Innmind/Router/workflows/CI/badge.svg)](https://github.com/Innmind/Router/actions?query=workflow%3ACI)
[![Type Coverage](https://shepherd.dev/github/Innmind/Router/coverage.svg)](https://shepherd.dev/github/Innmind/Router)

Simple router using [url templates](https://github.com/Innmind/UrlTemplate) as route patterns.

## Installation

```sh
composer require innmind/router
```

## Usage

```php
use Innmind\Router\{
    Route,
    Route\Name,
    RequestMatcher\RequestMatcher,
    UrlGenerator\UrlGenerator,
};
use Innmind\Http\Message\{
    Method,
    ServerRequest,
};
use Innmind\UrlTemplate\Template;
use Innmind\Url\Url;
use Innmind\Immutable\{
    Sequence,
    Maybe,
};

$routes = Sequence::of(
    Route::of(
        Method::post,
        Template::of('/url{/template}'),
    )->named(Name::of('routeName')),
    Route::of(
        Method::delete,
        Template::of('/resource/{id}'),
    )->named(Name::of('anotherRoute')),
);

$requestMatcher = new RequestMatcher($routes);
$route = $requestMatcher(/* instance of ServerRequest */); // Maybe<Route>
$urlGenerator = new UrlGenerator($routes);
$urlGenerator(Name::of('routeName')); // Url or throws NoMatchingRouteFound
```

### Building a simple app

Example using the `innmind/http-server` package to respond with files stored in a private folder.

```php
use Innmind\Router\{
    Route,
    RequestMatcher\RequestMatcher,
};
use Innmind\HttpServer\Main;
use Innmind\OperatingSystem\OperatingSystem;
use Innmind\Http\{
    Message\Method,
    Message\ServerRequest,
    Message\Response\Response,
    Message\StatusCode,
};
use Innmind\Filesystem\Name;
use Innmind\UrlTemplate\Template;
use Innmind\Url\Path;

new class extends Main {
    private RequestMatcher $router;

    protected function preload(OperatingSystem $os): void
    {
        $routes = Sequence::of(
            Route::of(Method::get, Template::of('/image/{name}'))->handle(
                fn($request, $variables) => $this->loadFile(
                    $os,
                    $variables->get('name'),
                ),
            ),
            Route::of(Method::get, Template::of('/image/random'))->handle(
                fn($request) => $this->loadFile(
                    $os,
                    generateRandomName(),
                ),
            ),
        );

        $this->router = new RequestMatcher($routes);
    }

    protected function main(ServerRequest $request): Response
    {
        return ($this->router)($request)->match(
            static fn($route) => $route->respondTo($request),
            static fn() => new Response(
                StatusCode::notFound,
                $request->protocolVersion(),
            ),
        );
    }

    private function loadFile(OperatingSystem $os, string $name): Response
    {
        return $os
            ->filesystem()
            ->mount(Path::of('some/private/folder/'))
            ->get(Name::of($name))
            ->match(
                static fn($file) => new Response(
                    StatusCode::ok,
                    $request->protocolVersion(),
                    null,
                    $file->content(),
                ),
                static fn() => new Response(
                    StatusCode::notFound,
                    $request->protocolVersion(),
                ),
            );
    }
}
```
