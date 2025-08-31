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

/**
 * @psalm-immutable
 */
final class Router
{
    /**
     * @param Component<SideEffect, Response> $component
     */
    private function __construct(
        private Component $component,
    ) {
    }

    /**
     * @return Attempt<Response>
     */
    #[\NoDiscard]
    public function __invoke(ServerRequest $request): Attempt
    {
        return ($this->component)($request, SideEffect::identity())->mapError(
            static fn($e) => match (true) {
                $e instanceof Exception\HandleError => $e->unwrap(),
                default => $e,
            },
        );
    }

    /**
     * @psalm-pure
     *
     * @param Component<SideEffect, Response> $component
     */
    #[\NoDiscard]
    public static function of(Component $component): self
    {
        return new self($component);
    }
}
