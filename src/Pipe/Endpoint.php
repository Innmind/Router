<?php
declare(strict_types = 1);

namespace Innmind\Router\Pipe;

use Innmind\Router\{
    Component,
    Component\Provider,
    Component\Like,
    Handle,
    Any,
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
            ->pipe(Handle::via($handle));
    }

    #[\NoDiscard]
    public function spread(): Endpoint\Spread
    {
        return Endpoint\Spread::of($this->endpoint);
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
        return $this->endpoint->pipe(
            Any::of($first, ...$rest),
        );
    }

    #[\Override]
    public function toComponent(): Component
    {
        return $this->endpoint;
    }
}
