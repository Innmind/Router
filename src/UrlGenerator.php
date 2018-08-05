<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Router\Route\Name;
use Innmind\Url\UrlInterface;
use Innmind\Immutable\MapInterface;

interface UrlGenerator
{
    /**
     * @param MapInterface<string, variable> $variables
     */
    public function __invoke(Name $route, MapInterface $variables = null): UrlInterface;
}
