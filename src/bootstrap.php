<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Router\{
    RequestMatcher\RequestMatcher,
    UrlGenerator\UrlGenerator,
    Loader\Yaml,
};
use Innmind\Url\Path;

function bootstrap(Path ...$routes): array
{
    $routes = (new Yaml)(...$routes);

    return [
        'requestMatcher' => new RequestMatcher($routes),
        'urlGenerator' => new UrlGenerator($routes),
    ];
}
