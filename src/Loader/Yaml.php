<?php
declare(strict_types = 1);

namespace Innmind\Router\Loader;

use Innmind\Router\{
    Loader,
    Route,
    Route\Name,
    Exception\DomainException,
};
use Innmind\Url\Path;
use Innmind\Immutable\{
    Set,
    Str,
};
use Symfony\Component\Yaml\Yaml as Parser;

final class Yaml implements Loader
{
    public function __invoke(Path ...$files): Set
    {
        /** @var Set<Route> */
        return Set::lazy(Route::class, function() use ($files): \Generator {
            foreach ($files as $file) {
                /** @var array<string|int, mixed> */
                $content = Parser::parseFile($file->toString());

                /** @var mixed $value */
                foreach ($content as $key => $value) {
                    if (!\is_string($key) || !\is_string($value)) {
                        throw new DomainException;
                    }

                    yield Route::of(
                        new Name($key),
                        Str::of($value),
                    );
                }
            }
        });
    }
}
