<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Url\Path;

/**
 * @return array{requestMatcher: RequestMatcher, urlGenerator: UrlGenerator}
 */
function bootstrap(Path ...$routes): array
{
    $routes = (new Loader\Yaml)(...$routes);

    return [
        'requestMatcher' => new RequestMatcher\RequestMatcher($routes),
        'urlGenerator' => new UrlGenerator\UrlGenerator($routes),
    ];
}
