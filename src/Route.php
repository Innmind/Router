<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\UrlTemplate\Template;

/**
 * This interface should be used on enums to provide named routes in a type safe
 * way.
 *
 * @psalm-immutable
 */
interface Route
{
    public function template(): Template;
}
