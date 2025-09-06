<?php
declare(strict_types = 1);

namespace Innmind\Router\Pipe;

use Innmind\Router\{
    Component,
    Component\Provider,
    Component\Like,
    Route,
    Endpoint,
    Handle,
};
use Innmind\UrlTemplate\Template;
use Innmind\Http;
use Innmind\Immutable\Attempt;

/**
 * @psalm-immutable
 * @implements Provider<mixed, Http\Method>
 */
final class Method implements Provider
{
    /** @use Like<mixed, Http\Method> */
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
     * @param literal-string|Template|Route $template
     */
    #[\NoDiscard]
    public function endpoint(string|Template|Route $template): Method\Endpoint
    {
        return Method\Endpoint::of(
            $this->method,
            Endpoint::of($template),
        );
    }

    /**
     * @param callable(Http\ServerRequest, Http\Method): Attempt<Http\Response> $handle
     *
     * @return Component<mixed, Http\Response>
     */
    #[\NoDiscard]
    public function handle(callable $handle): Component
    {
        return $this
            ->toComponent()
            ->pipe(Handle::via($handle));
    }

    #[\Override]
    public function toComponent(): Component
    {
        return $this->method;
    }
}
