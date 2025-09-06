<?php
declare(strict_types = 1);

namespace Innmind\Router\Pipe\Forward\Method;

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
     * @param Component<Map<string, string>, Map<string, string>> $previous
     */
    private function __construct(
        private Component $previous,
    ) {
    }

    /**
     * @internal
     * @psalm-pure
     *
     * @param Component<Map<string, string>, Map<string, string>> $previous
     */
    #[\NoDiscard]
    public static function of(Component $previous): self
    {
        return new self($previous);
    }

    /**
     * @param Handle\Proxy|(callable(mixed...): Attempt<Http\Response>) $handle
     *
     * @return Component<Map<string, string>, Http\Response>
     */
    #[\NoDiscard]
    public function handle(Handle\Proxy|callable $handle): Component
    {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        return $this->previous->feed(Handle::of($handle));
    }
}
