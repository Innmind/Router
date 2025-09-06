<?php
declare(strict_types = 1);

namespace Innmind\Router\Pipe\Method\Endpoint;

use Innmind\Router\{
    Component,
    Handle,
};
use Innmind\Http\{
    Response,
    Method,
};
use Innmind\Immutable\{
    Map,
    Attempt,
};

/**
 * @psalm-immutable
 */
final class Spread
{
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
     * @param Handle\Proxy|(callable(mixed...): Attempt<Response>) $handle
     *
     * @return Component<mixed, Response>
     */
    #[\NoDiscard]
    public function handle(Handle\Proxy|callable $handle): Component
    {
        /** @psalm-suppress MixedArgumentTypeCoercion Don't know why it complains */
        return $this
            ->method
            ->feed($this->endpoint)
            ->feed(Handle::of($handle));
    }
}
