<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Router\Route\Name;
use Innmind\UrlTemplate\Template;
use Innmind\Http\Message\{
    ServerRequest,
    Method,
    Response,
    StatusCode,
};
use Innmind\Immutable\Maybe;

/**
 * @psalm-immutable
 */
final class Route
{
    /** @var Maybe<Name> */
    private Maybe $name;
    private Template $template;
    private Method $method;
    /** @var callable(ServerRequest): Response */
    private $handler;

    /**
     * @param Maybe<Name> $name
     * @param callable(ServerRequest): Response $handler
     */
    private function __construct(
        Maybe $name,
        Method $method,
        Template $template,
        callable $handler,
    ) {
        $this->name = $name;
        $this->method = $method;
        $this->template = $template;
        $this->handler = $handler;
    }

    /**
     * @psalm-pure
     */
    public static function of(Method $method, Template $template): self
    {
        /** @var Maybe<Name> */
        $name = Maybe::nothing();

        return new self(
            $name,
            $method,
            $template,
            static fn(ServerRequest $request) => new Response\Response(
                StatusCode::ok,
                $request->protocolVersion(),
            ),
        );
    }

    public function named(Name $name): self
    {
        return new self(
            Maybe::just($name),
            $this->method,
            $this->template,
            $this->handler,
        );
    }

    public function is(Name $name): bool
    {
        return $this->name->match(
            static fn($self) => $self->equals($name),
            static fn() => false,
        );
    }

    public function template(): Template
    {
        return $this->template;
    }

    /**
     * @param callable(ServerRequest): Response $handler
     */
    public function handle(callable $handler): self
    {
        return new self(
            $this->name,
            $this->method,
            $this->template,
            $handler,
        );
    }

    public function matches(ServerRequest $request): bool
    {
        if ($request->method() !== $this->method) {
            return false;
        }

        return $this->template->matches(
            $request
                ->url()
                ->withoutScheme()
                ->withoutAuthority(),
        );
    }

    public function respondTo(ServerRequest $request): Response
    {
        /** @psalm-suppress ImpureFunctionCall For real apps the handler can't really be pure */
        return ($this->handler)($request);
    }
}
