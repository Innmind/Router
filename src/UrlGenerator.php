<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Router\{
    Route\Name,
    Exception\NoMatchingRouteFound,
};
use Innmind\Url\Url;
use Innmind\Immutable\Map;

interface UrlGenerator
{
    /**
     * @param ?Map<non-empty-string, string|list<string>|list<array{string, string}>> $variables
     *
     * @throws NoMatchingRouteFound
     */
    public function __invoke(Name $route, Map $variables = null): Url;
}
