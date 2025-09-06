<?php
declare(strict_types = 1);

namespace Innmind\Router\Pipe\Endpoint;

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
 * @implements Provider<mixed, Map<string, string>>
 */
final class Method implements Provider
{
    /** @use Like<mixed, Map<string, string>> */
    use Like;

    /**
     * @param Component<mixed, Map<string, string>> $endpoint
     * @param Component<mixed, Http\Method> $method
     */
    private function __construct(
        private Component $endpoint,
        private Component $method,
    ) {
    }

    /**
     * @internal
     * @psalm-pure
     *
     * @param Component<mixed, Map<string, string>> $endpoint
     * @param Component<mixed, Http\Method> $method
     */
    #[\NoDiscard]
    public static function of(Component $endpoint, Component $method): self
    {
        return new self($endpoint, $method);
    }

    /**
     * @param callable(Http\ServerRequest, Map<string, string>): Attempt<Http\Response> $handle
     *
     * @return Component<mixed, Http\Response>
     */
    #[\NoDiscard]
    public function handle(callable $handle): Component
    {
        return $this
            ->toComponent()
            ->feed(Handle::via($handle));
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
         */
        return $this->endpoint->guard(
            static fn($input) => $method->map(static fn() => $input),
        );
    }
}
