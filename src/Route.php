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

final class Route
{
    private $name;
    private $template;
    private $method;

    public function __construct(Name $name, Template $template, Method $method)
    {
        $this->name = $name;
        $this->template = $template;
        $this->method = $method;
    }

    public static function of(Name $name, Str $pattern): self
    {
        [$method, $template] = $pattern->split(' ');

        return new self(
            $name,
            Template::of((string) $template),
            new Method\Method((string) $method->toUpper())
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
        if ((string) $request->method() !== (string) $this->method) {
            return false;
        }

        return $this->template->matches($request->url());
    }
}
