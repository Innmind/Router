<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Http\ServerRequest;
use Innmind\Immutable\Attempt;

/**
 * @template I
 * @template O
 * @psalm-immutable
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
    #[\NoDiscard]
    public function __invoke(ServerRequest $request, mixed $input): Attempt
    {
        /** @psalm-suppress ImpureFunctionCall */
        return ($this->implementation)($request, $input);
    }

    /**
     * @template A
     * @template B
     * @psalm-pure
     *
     * @param callable(ServerRequest, A): Attempt<B> $component
     *
     * @return self<A, B>
     */
    #[\NoDiscard]
    public static function of(callable $component): self
    {
        return new self(\Closure::fromCallable($component));
    }

    /**
     * @template T
     *
     * @param callable(O): T $map
     *
     * @return self<I, T>
     */
    #[\NoDiscard]
    public function map(callable $map): self
    {
        $previous = $this->implementation;

        /** @psalm-suppress MixedArgument */
        return new self(
            static fn($request, $input) => $previous($request, $input)->map($map),
        );
    }

    /**
     * @template T
     *
     * @param callable(O): self<O, T> $map
     *
     * @return self<I, T>
     */
    #[\NoDiscard]
    public function flatMap(callable $map): self
    {
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
    #[\NoDiscard]
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
    #[\NoDiscard]
    public function otherwise(callable $recover): self
    {
        $previous = $this->implementation;

        // Never try to recover from a handle error as it may lead to another
        // handle being called
        /** @psalm-suppress MixedArgument */
        return new self(
            static fn(ServerRequest $request, mixed $input) => $previous($request, $input)->recover(
                static fn($error) => match (true) {
                    $error instanceof Exception\HandleError => Attempt::error($error),
                    default => $recover($error)($request, $input),
                },
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
    #[\NoDiscard]
    public function or(self $component): self
    {
        return $this->otherwise(static fn() => $component);
    }

    /**
     * @param callable(\Throwable): \Throwable $map
     *
     * @return self<I, O>
     */
    #[\NoDiscard]
    public function mapError(callable $map): self
    {
        $previous = $this->implementation;

        /** @psalm-suppress MixedArgument */
        return new self(
            static fn($request, $input) => $previous($request, $input)->mapError($map),
        );
    }
}
