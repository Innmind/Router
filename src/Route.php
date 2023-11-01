<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Router\{
    Route\Name,
    Route\Variables,
    Exception\LogicException,
};
use Innmind\UrlTemplate\Template;
use Innmind\Http\{
    ServerRequest,
    Method,
    Response,
    Response\StatusCode,
};
use Innmind\Url\Url;
use Innmind\Immutable\{
    Maybe,
    Str,
};

/**
 * @psalm-immutable
 */
final class Route
{
    /** @var Maybe<Name> */
    private Maybe $name;
    private Template $template;
    private Method $method;
    /** @var callable(ServerRequest, Variables): Response */
    private $handler;

    /**
     * @param Maybe<Name> $name
     * @param callable(ServerRequest, Variables): Response $handler
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
            static fn(ServerRequest $request) => Response::of(
                StatusCode::ok,
                $request->protocolVersion(),
            ),
        );
    }

    /**
     * @psalm-pure
     *
     * @param literal-string $pattern
     *
     * @throws LogicException If the pattern is invalid
     */
    public static function literal(string $pattern): self
    {
        $chunks = Str::of($pattern)->split(' ');

        $method = $chunks
            ->first()
            ->map(static fn($method) => $method->toUpper()->toString())
            ->flatMap(Method::maybe(...));
        $template = Str::of(' ')
            ->join(
                $chunks
                    ->drop(1)
                    ->map(static fn($chunk) => $chunk->toString()),
            )
            ->toString();

        return Maybe::all($method, Template::maybe($template))
            ->map(self::of(...))
            ->match(
                static fn($self) => $self,
                static fn() => throw new LogicException($pattern),
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
     * @param callable(ServerRequest, Variables): Response $handler
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

        return $this->template->matches($this->url($request));
    }

    public function respondTo(ServerRequest $request): Response
    {
        /** @psalm-suppress ImpureFunctionCall For real apps the handler can't really be pure */
        return ($this->handler)(
            $request,
            Variables::of($this->template->extract($this->url($request))),
        );
    }

    private function url(ServerRequest $request): Url
    {
        return $request
            ->url()
            ->withoutScheme()
            ->withoutAuthority();
    }
}
