<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Router\Component\Provider;
use Innmind\Http\{
    ServerRequest,
    Response,
};
use Innmind\UrlTemplate\Template;
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
     * @param literal-string|Template|Route $template
     */
    #[\NoDiscard]
    public function endpoint(string|Template|Route $template): Pipe\Endpoint
    {
        return Pipe\Endpoint::of(Endpoint::of($template));
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

    /**
     * @param Component<mixed, Response>|Provider<mixed, Response> $first
     * @param Component<mixed, Response>|Provider<mixed, Response> $rest
     *
     * @return Component<mixed, Response>
     */
    public function any(
        Component|Provider $first,
        Component|Provider ...$rest,
    ): Component {
        return Any::of($first, ...$rest);
    }

    /**
     * Use this pipe to build components for Pipe->endpoint()->any()
     */
    #[\NoDiscard]
    public function forward(): Pipe\Forward
    {
        return Pipe\Forward::new();
    }
}
