<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Http\{
    ServerRequest,
    Response,
};
use Innmind\Immutable\Attempt;

/**
 * @psalm-immutable
 */
final class Pipe
{
    private function __construct()
    {
    }

    /**
     * @psalm-pure
     */
    #[\NoDiscard]
    public static function new(): self
    {
        return new self;
    }

    #[\NoDiscard]
    public function get(): Pipe\Method
    {
        return Pipe\Method::of(Method::get());
    }

    #[\NoDiscard]
    public function post(): Pipe\Method
    {
        return Pipe\Method::of(Method::post());
    }

    #[\NoDiscard]
    public function put(): Pipe\Method
    {
        return Pipe\Method::of(Method::put());
    }

    #[\NoDiscard]
    public function patch(): Pipe\Method
    {
        return Pipe\Method::of(Method::patch());
    }

    #[\NoDiscard]
    public function delete(): Pipe\Method
    {
        return Pipe\Method::of(Method::delete());
    }

    #[\NoDiscard]
    public function options(): Pipe\Method
    {
        return Pipe\Method::of(Method::options());
    }

    #[\NoDiscard]
    public function trace(): Pipe\Method
    {
        return Pipe\Method::of(Method::trace());
    }

    #[\NoDiscard]
    public function connect(): Pipe\Method
    {
        return Pipe\Method::of(Method::connect());
    }

    #[\NoDiscard]
    public function head(): Pipe\Method
    {
        return Pipe\Method::of(Method::head());
    }

    #[\NoDiscard]
    public function link(): Pipe\Method
    {
        return Pipe\Method::of(Method::link());
    }

    #[\NoDiscard]
    public function unlink(): Pipe\Method
    {
        return Pipe\Method::of(Method::unlink());
    }

    /**
     * @param callable(ServerRequest): Attempt<Response> $handle
     *
     * @return Component<mixed, Response>
     */
    #[\NoDiscard]
    public function handle(callable $handle): Component
    {
        /** @psalm-suppress PossiblyInvalidArgument */
        return Handle::via($handle);
    }
}
