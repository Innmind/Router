<?php
declare(strict_types = 1);

namespace Innmind\Router\Loader;

use Innmind\Router\{
    Loader,
    Route,
    Route\Name,
    Exception\DomainException,
};
use Innmind\Url\PathInterface;
use Innmind\Immutable\{
    SetInterface,
    Set,
    Str,
};
use Symfony\Component\Yaml\Yaml as Parser;

final class Yaml implements Loader
{
    public function __invoke(PathInterface ...$files): SetInterface
    {
        $routes = Set::of(Route::class);

        foreach ($files as $file) {
            $content = Parser::parseFile((string) $file);

            foreach ($content as $key => $value) {
                if (!is_string($key) || !is_string($value)) {
                    throw new DomainException;
                }

                $routes = $routes->add(Route::of(
                    new Name($key),
                    Str::of($value)
                ));
            }
        }

        return $routes;
    }
}
