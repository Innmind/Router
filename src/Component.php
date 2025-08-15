<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Http\ServerRequest;
use Innmind\Immutable\Attempt;

/**
 * @template I
 * @template O
 */
final class Component
{
    /**
     * @param \Closure(ServerRequest, I): Attempt<O> $implementation
     */
    private function __construct(
        private \Closure $implementation,
    ) {
    }

    /**
     * @param I $input
     *
     * @return Attempt<O>
     */
    public function __invoke(ServerRequest $request, mixed $input): Attempt
    {
        return ($this->implementation)($request, $input);
    }

    /**
     * @template T
     *
     * @param callable(O): self<O, T> $map
     *
     * @return self<I, T>
     */
    public function flatMap(callable $map): self
    {
        $map = \Closure::fromCallable($map);
        $previous = $this->implementation;

        /** @psalm-suppress MixedArgument */
        return new self(
            static fn(ServerRequest $request, mixed $input) => $previous($request, $input)->flatMap(
                static fn($output) => $map($output)($request, $output),
            ),
        );
    }

    /**
     * @template T
     *
     * @param self<O, T> $component
     *
     * @return self<I, T>
     */
    public function pipe(self $component): self
    {
        return $this->flatMap(static fn() => $component);
    }

    /**
     * @template T
     *
     * @param callable(\Throwable): self<I, T> $recover
     *
     * @return self<I, T>
     */
    public function otherwise(callable $recover): self
    {
        $recover = \Closure::fromCallable($recover);
        $previous = $this->implementation;

        /** @psalm-suppress MixedArgument */
        return new self(
            static fn(ServerRequest $request, mixed $input) => $previous($request, $input)->recover(
                static fn($error) => $recover($error)($request, $input),
            ),
        );
    }

    /**
     * @template T
     *
     * @param self<I, T> $component
     *
     * @return self<I, T>
     */
    public function or(self $component): self
    {
        return $this->otherwise(static fn() => $component);
    }
}
