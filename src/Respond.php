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
    #[\NoDiscard]
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
    #[\NoDiscard]
    public static function notFound(): Component
    {
        return self::with(StatusCode::notFound);
    }

    /**
     * @psalm-pure
     *
     * @return callable(\Throwable): Component<mixed, Response>
     */
    #[\NoDiscard]
    public static function withHttpErrors(): callable
    {
        return static fn(\Throwable $e) => Component::of(
            static fn($request) => match (true) {
                $e instanceof Exception\MethodNotAllowed => Attempt::result(Response::of(
                    StatusCode::methodNotAllowed,
                    $request->protocolVersion(),
                )),
                $e instanceof Exception\NotFound => Attempt::result(Response::of(
                    StatusCode::notFound,
                    $request->protocolVersion(),
                )),
                default => Attempt::error($e),
            },
        );
    }
}
