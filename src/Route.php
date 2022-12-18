<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Router\Route\Name;
use Innmind\UrlTemplate\Template;
use Innmind\Http\Message\{
    ServerRequest,
    Method,
};

/**
 * @psalm-immutable
 */
final class Route
{
    private Name $name;
    private Template $template;
    private Method $method;

    private function __construct(Name $name, Method $method, Template $template)
    {
        $this->name = $name;
        $this->method = $method;
        $this->template = $template;
    }

    /**
     * @psalm-pure
     */
    public static function of(Name $name, Method $method, Template $template): self
    {
        return new self($name, $method, $template);
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
}
