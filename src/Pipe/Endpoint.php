<?php
declare(strict_types = 1);

namespace Innmind\Router\Pipe;

use Innmind\Router\{
    Component,
    Component\Provider,
    Component\Like,
    Handle,
    Any,
    Method,
};
use Innmind\Http\{
    ServerRequest,
    Response,
};
use Innmind\Immutable\{
    Map,
    Attempt,
};

/**
 * @psalm-immutable
 * @implements Provider<mixed, Map<string, string>>
 */
final class Endpoint implements Provider
{
    /** @use Like<mixed, Map<string, string>> */
    use Like;

    /**
     * @param Component<mixed, Map<string, string>> $endpoint
     */
    private function __construct(
        private Component $endpoint,
    ) {
    }

    /**
     * @internal
     * @psalm-pure
     *
     * @param Component<mixed, Map<string, string>> $endpoint
     */
    #[\NoDiscard]
    public static function of(Component $endpoint): self
    {
        return new self($endpoint);
    }

    /**
     * @param callable(ServerRequest, Map<string, string>): Attempt<Response> $handle
     *
     * @return Component<mixed, Response>
     */
    #[\NoDiscard]
    public function handle(callable $handle): Component
    {
        return $this
            ->toComponent()
            ->feed(Handle::via($handle));
    }

    #[\NoDiscard]
    public function spread(): Endpoint\Spread
    {
        return Endpoint\Spread::of($this->endpoint);
    }

    #[\NoDiscard]
    public function get(): Endpoint\Method
    {
        return Endpoint\Method::of(
            $this->endpoint,
            Method::get(),
        );
    }

    #[\NoDiscard]
    public function post(): Endpoint\Method
    {
        return Endpoint\Method::of(
            $this->endpoint,
            Method::post(),
        );
    }

    #[\NoDiscard]
    public function put(): Endpoint\Method
    {
        return Endpoint\Method::of(
            $this->endpoint,
            Method::put(),
        );
    }

    #[\NoDiscard]
    public function patch(): Endpoint\Method
    {
        return Endpoint\Method::of(
            $this->endpoint,
            Method::patch(),
        );
    }

    #[\NoDiscard]
    public function delete(): Endpoint\Method
    {
        return Endpoint\Method::of(
            $this->endpoint,
            Method::delete(),
        );
    }

    #[\NoDiscard]
    public function options(): Endpoint\Method
    {
        return Endpoint\Method::of(
            $this->endpoint,
            Method::options(),
        );
    }

    #[\NoDiscard]
    public function trace(): Endpoint\Method
    {
        return Endpoint\Method::of(
            $this->endpoint,
            Method::trace(),
        );
    }

    #[\NoDiscard]
    public function connect(): Endpoint\Method
    {
        return Endpoint\Method::of(
            $this->endpoint,
            Method::connect(),
        );
    }

    #[\NoDiscard]
    public function head(): Endpoint\Method
    {
        return Endpoint\Method::of(
            $this->endpoint,
            Method::head(),
        );
    }

    #[\NoDiscard]
    public function link(): Endpoint\Method
    {
        return Endpoint\Method::of(
            $this->endpoint,
            Method::link(),
        );
    }

    #[\NoDiscard]
    public function unlink(): Endpoint\Method
    {
        return Endpoint\Method::of(
            $this->endpoint,
            Method::unlink(),
        );
    }

    /**
     * @no-named-arguments
     *
     * @param Component<Map<string, string>, Response>|Provider<Map<string, string>, Response> $first
     * @param Component<Map<string, string>, Response>|Provider<Map<string, string>, Response> $rest
     *
     * @return Component<mixed, Response>
     */
    public function any(
        Component|Provider $first,
        Component|Provider ...$rest,
    ): Component {
        return $this->endpoint->feed(
            Any::of($first, ...$rest),
        );
    }

    #[\Override]
    public function toComponent(): Component
    {
        return $this->endpoint;
    }
}
