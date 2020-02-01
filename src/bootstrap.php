<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Router\{
    RequestMatcher\RequestMatcher,
    UrlGenerator\UrlGenerator,
    Loader\Yaml,
};
use Innmind\Url\Path;
use Innmind\Immutable\Set;
use function Innmind\Immutable\unwrap;

/**
 * @param Set<Path> $routes
 */
function bootstrap(Set $routes): array
{
    $routes = (new Yaml)(...unwrap($routes));

    return [
        'requestMatcher' => new RequestMatcher($routes),
        'urlGenerator' => new UrlGenerator($routes),
    ];
}
