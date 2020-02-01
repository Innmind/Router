<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Router\Route\Name;
use Innmind\UrlTemplate\Template;
use Innmind\Http\Message\{
    ServerRequest,
    Method,
};
use Innmind\Immutable\Str;
use function Innmind\Immutable\unwrap;

final class Route
{
    private Name $name;
    private Template $template;
    private Method $method;

    public function __construct(Name $name, Template $template, Method $method)
    {
        $this->name = $name;
        $this->template = $template;
        $this->method = $method;
    }

    public static function of(Name $name, Str $pattern): self
    {
        [$method, $template] = unwrap($pattern->split(' '));

        return new self(
            $name,
            Template::of($template->toString()),
            new Method($method->toUpper()->toString()),
        );
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function template(): Template
    {
        return $this->template;
    }

    public function matches(ServerRequest $request): bool
    {
        if ($request->method()->toString() !== $this->method->toString()) {
            return false;
        }

        return $this->template->matches(
            $request
                ->url()
                ->withoutScheme()
                ->withoutAuthority(),
        );
    }
}
