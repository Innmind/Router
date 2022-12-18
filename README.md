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
        Name::of('routeName'),
        Method::post,
        Template::of('/url{/template}'),
    ),
    Route::of(
        Name::of('anotherRoute'),
        Method::delete,
        Template::of('/resource/{id}'),
    ),
);

$requestMatcher = new RequestMatcher($routes);
$route = $requestMatcher(/* instance of ServerRequest */); // Maybe<Route>
$urlGenerator = new UrlGenerator($routes);
$urlGenerator(Name::of('routeName')); // Url or throws NoMatchingRouteFound
```
