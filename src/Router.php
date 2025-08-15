<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Http\{
    ServerRequest,
    Response,
};
use Innmind\Immutable\{
    Attempt,
    SideEffect,
};

final class Router
{
    /**
     * @param Component<SideEffect, Response> $component
     */
    private function __construct(
        private Component $component,
    ) {
    }

    public function __invoke(ServerRequest $request): Attempt
    {
        return ($this->component)($request, SideEffect::identity());
    }

    /**
     * @param Component<SideEffect, Response> $component
     */
    public static function of(Component $component): self
    {
        return new self($component);
    }
}
