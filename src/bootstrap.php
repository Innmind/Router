<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Router\{
    RequestMatcher\RequestMatcher,
    UrlGenerator\UrlGenerator,
    Loader\Yaml,
};
use Innmind\Immutable\SetInterface;

/**
 * @param SetInterface<PathInterface> $routes
 */
function bootstrap(SetInterface $routes): array
{
    $routes = (new Yaml)(...$routes);

    return [
        'requestMatcher' => new RequestMatcher($routes),
        'urlGenerator' => new UrlGenerator($routes),
    ];
}
