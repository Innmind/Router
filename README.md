# Router

[![codecov](https://codecov.io/gh/Innmind/Router/branch/develop/graph/badge.svg)](https://codecov.io/gh/Innmind/Router)
[![Build Status](https://github.com/Innmind/Router/workflows/CI/badge.svg)](https://github.com/Innmind/Router/actions?query=workflow%3ACI)
[![Type Coverage](https://shepherd.dev/github/Innmind/Router/coverage.svg)](https://shepherd.dev/github/Innmind/Router)

Simple router using [url templates](https://github.com/Innmind/UrlTemplate) as route patterns.

## Installation

```sh
composer require innmind/router
```

## Usage

```php
use function Innmind\Router\bootstrap;
use Innmind\Router\Route\Name;
use Innmind\Url\{
    Url,
    Path,
};

$router = bootstrap(
    new Path('/to/routes/definitions.yml')
);
$route = $router['requestMatcher']($serverRequest); // Route or throws NoMatchingRouteFound
$router['urlGenerator'](new Name('routeName')); // Url
```

The routes definitions must look like this:

```yaml
routeName: POST /url{/template}
anotherRoute: DELETE /resource/{id}
```
