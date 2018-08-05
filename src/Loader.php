<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Url\PathInterface;
use Innmind\Immutable\SetInterface;

interface Loader
{
    /**
     * @return SetInterface<Route>
     */
    public function __invoke(PathInterface ...$files): SetInterface;
}
