<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Http\{
    ServerRequest,
    Response,
};
use Innmind\Immutable\Attempt;

final class Handle
{
    /**
     * @template I
     *
     * @param callable(ServerRequest, I): Attempt<Response> $handler
     *
     * @return Component<I, Response>
     */
    public static function via(callable $handler): Component
    {
        $handler = \Closure::fromCallable($handler);

        /**
         * @psalm-suppress PossiblyNullFunctionCall
         * @psalm-suppress MixedReturnStatement
         * @psalm-suppress InaccessibleMethod
         */
        return (\Closure::bind(
            static fn() => new Component($handler),
            null,
            Component::class,
        ))();
    }
}
