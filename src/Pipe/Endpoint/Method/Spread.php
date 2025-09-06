<?php
declare(strict_types = 1);

namespace Innmind\Router\Pipe\Endpoint\Method;

use Innmind\Router\{
    Component,
    Handle,
};
use Innmind\Http;
use Innmind\Immutable\{
    Attempt,
    Map,
};

/**
 * @psalm-immutable
 */
final class Spread
{
    /**
     * @param Component<mixed, Map<string, string>> $previous
     */
    private function __construct(
        private Component $previous,
    ) {
    }

    /**
     * @internal
     * @psalm-pure
     *
     * @param Component<mixed, Map<string, string>> $previous
     */
    #[\NoDiscard]
    public static function of(Component $previous): self
    {
        return new self($previous);
    }

    /**
     * @param callable(...mixed): Attempt<Http\Response> $handle
     *
     * @return Component<mixed, Http\Response>
     */
    #[\NoDiscard]
    public function handle(callable $handle): Component
    {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        return $this->previous->feed(Handle::of($handle));
    }
}
