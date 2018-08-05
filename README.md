# Router

| `master` | `develop` |
|----------|-----------|
| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/Router/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Router/?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/Router/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Router/?branch=develop) |
| [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/Router/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Router/?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/Router/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Router/?branch=develop) |
| [![Build Status](https://scrutinizer-ci.com/g/Innmind/Router/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Router/build-status/master) | [![Build Status](https://scrutinizer-ci.com/g/Innmind/Router/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Router/build-status/develop) |

Simple router using [url templates](https://github.com/Innmind/UrlTemplate) as route patterns.

## Installation

```sh
composer require innmind/router
```

## Usage

```php
use Innmind\Compose\ContainerBuilder\ContainerBuilder;
use Innmind\Url\{
    UrlInterface,
    PathInterface,
    Path,
};
use Innmind\Immutable\{
    Map,
    Set,
};
use Innmind\Router\Route\Name;

$container = (new ContainerBuilder)(
    new Path('container.yml'),
    (new Map('string', 'mixed'))
        ->put(
            'routes',
            Set::of(
                PathInterface::class,
                new Path('/to/routes/definitions.yml')
            )
        )
);
$route = $container->get('requestMatcher')($serverRequest); // Route or throws NoMatchingRouteFound
$container->get('urlGenerator')(new Name('routeName')); // UrlInterface
```

The routes definitions must look like this:

```yaml
routeName: POST /url{/template}
anotherRoute: DELETE /resource/{id}
```
