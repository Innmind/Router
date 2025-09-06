<?php
declare(strict_types = 1);

namespace Innmind\Router\Pipe\Endpoint;

use Innmind\Router\{
    Component,
    Handle,
};
use Innmind\Http\Response;
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
     * @param callable(...mixed): Attempt<Response> $handle
     *
     * @return Component<mixed, Response>
     */
    public function handle(callable $handle): Component
    {
        /** @psalm-suppress MixedArgumentTypeCoercion Don't know why it complains */
        return $this
            ->endpoint
            ->feed(Handle::of($handle));
    }
}
