<?php
declare(strict_types = 1);

namespace Innmind\Router\Handle;

use Innmind\Http\Response;
use Innmind\Immutable\Attempt;

final class Proxy
{
    /**
     * @param \Closure(): (callable(mixed...): Attempt<Response>) $load
     */
    private function __construct(
        private \Closure $load,
    ) {
    }

    /**
     * @param callable(): (callable(mixed...): Attempt<Response>) $load
     */
    public static function of(callable $load): self
    {
        return new self(\Closure::fromCallable($load));
    }

    /**
     * @return callable(...mixed): Attempt<Response>
     */
    public function unwrap(): callable
    {
        return ($this->load)();
    }
}
