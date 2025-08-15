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
        return Component::of($handler);
    }
}
