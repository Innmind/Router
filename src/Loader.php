<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Url\Path;
use Innmind\Immutable\Set;

interface Loader
{
    /**
     * @return Set<Route>
     */
    public function __invoke(Path ...$files): Set;
}
