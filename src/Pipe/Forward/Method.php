<?php
declare(strict_types = 1);

namespace Innmind\Router\Pipe\Forward;

use Innmind\Router\{
    Component,
    Component\Provider,
    Component\Like,
    Handle,
};
use Innmind\Http;
use Innmind\Immutable\{
    Attempt,
    Map,
};

/**
 * @psalm-immutable
 * @implements Provider<Map<string, string>, Map<string, string>>
 */
final class Method implements Provider
{
    /** @use Like<Map<string, string>, Map<string, string>> */
    use Like;

    /**
     * @param Component<mixed, Http\Method> $method
     */
    private function __construct(
        private Component $method,
    ) {
    }

    /**
     * @internal
     * @psalm-pure
     *
     * @param Component<mixed, Http\Method> $method
     */
    #[\NoDiscard]
    public static function of(Component $method): self
    {
        return new self($method);
    }

    /**
     * @param callable(Http\ServerRequest, Map<string, string>): Attempt<Http\Response> $handle
     *
     * @return Component<Map<string, string>, Http\Response>
     */
    #[\NoDiscard]
    public function handle(callable $handle): Component
    {
        return $this
            ->toComponent()
            ->pipe(Handle::via($handle));
    }

    public function spread(): Method\Spread
    {
        return Method\Spread::of($this->toComponent());
    }

    #[\Override]
    public function toComponent(): Component
    {
        $method = $this->method;

        /**
         * @psalm-suppress MixedArgumentTypeCoercion
         * @var Component<Map<string, string>, Map<string, string>>
         */
        return Component::of(static fn($_, Map $input) => Attempt::result($input))
            ->flatMap(static fn($input) => $method->map(static fn() => $input));
    }
}
