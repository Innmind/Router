<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Http\{
    Response,
    Response\StatusCode,
};
use Innmind\Immutable\Attempt;

final class Respond
{
    /**
     * @psalm-pure
     *
     * @return Component<mixed, Response>
     */
    public static function with(StatusCode $status): Component
    {
        return Component::of(static fn($request) => Attempt::result(Response::of(
            $status,
            $request->protocolVersion(),
        )));
    }

    /**
     * @psalm-pure
     *
     * @return Component<mixed, Response>
     */
    public static function notFound(): Component
    {
        return self::with(StatusCode::notFound);
    }
}
