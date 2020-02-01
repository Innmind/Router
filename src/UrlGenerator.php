<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Router\Route\Name;
use Innmind\Url\Url;
use Innmind\Immutable\Map;

interface UrlGenerator
{
    /**
     * @param Map<string, scalar|array> $variables
     */
    public function __invoke(Name $route, Map $variables = null): Url;
}
