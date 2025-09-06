<?php
declare(strict_types = 1);

namespace Innmind\Router\Pipe\Method;

use Innmind\Router\{
    Component,
    Component\Provider,
    Component\Like,
    Handle,
};
use Innmind\Http\{
    Response,
    ServerRequest,
    Method,
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
     * @param Component<mixed, Method> $method
     * @param Component<mixed, Map<string, string>> $endpoint
     */
    private function __construct(
        private Component $method,
        private Component $endpoint,
    ) {
    }

    /**
     * @internal
     * @psalm-pure
     *
     * @param Component<mixed, Method> $method
     * @param Component<mixed, Map<string, string>> $endpoint
     */
    public static function of(
        Component $method,
        Component $endpoint,
    ): self {
        return new self($method, $endpoint);
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
        return Endpoint\Spread::of(
            $this->method,
            $this->endpoint,
        );
    }

    #[\Override]
    public function toComponent(): Component
    {
        /** @psalm-suppress MixedArgumentTypeCoercion Don't know why it complains */
        return $this->method->feed($this->endpoint);
    }
}
