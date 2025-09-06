<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Router\Component\Provider;
use Innmind\Http\ServerRequest;
use Innmind\Immutable\Attempt;

/**
 * @template-covariant I
 * @template-covariant O
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
     * @param callable(O): (self<O, T>|Provider<O, T>) $map
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
                static fn($output) => self::collapse($map($output))(
                    $request,
                    $output,
                ),
            ),
        );
    }

    /**
     * @template T
     *
     * @param callable(O): (self<O, T>|Provider<O, T>) $map
     *
     * @return self<I, T>
     */
    #[\NoDiscard]
    public function guard(callable $map): self
    {
        $previous = $this->implementation;

        /** @psalm-suppress MixedArgument */
        return new self(
            static fn(ServerRequest $request, mixed $input) => $previous($request, $input)->guard(
                static fn($output) => self::collapse($map($output))(
                    $request,
                    $output,
                ),
            ),
        );
    }

    /**
     * @template T
     *
     * @param self<O, T>|Provider<O, T> $component
     *
     * @return self<I, T>
     */
    #[\NoDiscard]
    public function pipe(self|Provider $component): self
    {
        return $this->flatMap(static fn() => $component);
    }

    /**
     * @template T
     *
     * @param self<O, T>|Provider<O, T> $component
     *
     * @return self<I, T>
     */
    #[\NoDiscard]
    public function feed(self|Provider $component): self
    {
        return $this->guard(static fn() => $component);
    }

    /**
     * @template T
     *
     * @param callable(\Throwable): (self<I, T>|Provider<I, T>) $recover
     *
     * @return self<I, T>
     */
    #[\NoDiscard]
    public function otherwise(callable $recover): self
    {
        $previous = $this->implementation;

        /** @psalm-suppress MixedArgument */
        return new self(
            static fn(ServerRequest $request, mixed $input) => $previous($request, $input)->recover(
                static fn($error) => self::collapse($recover($error))(
                    $request,
                    $input,
                ),
            ),
        );
    }

    /**
     * @template T
     *
     * @param callable(\Throwable): (self<I, T>|Provider<I, T>) $recover
     *
     * @return self<I, T>
     */
    #[\NoDiscard]
    public function xotherwise(callable $recover): self
    {
        $previous = $this->implementation;

        /** @psalm-suppress MixedArgument */
        return new self(
            static fn(ServerRequest $request, mixed $input) => $previous($request, $input)->xrecover(
                static fn($error) => self::collapse($recover($error))(
                    $request,
                    $input,
                ),
            ),
        );
    }

    /**
     * @template T
     *
     * @param self<I, T>|Provider<I, T> $component
     *
     * @return self<I, T>
     */
    #[\NoDiscard]
    public function or(self|Provider $component): self
    {
        return $this->otherwise(static fn() => $component);
    }

    /**
     * @template T
     *
     * @param self<I, T>|Provider<I, T> $component
     *
     * @return self<I, T>
     */
    #[\NoDiscard]
    public function xor(self|Provider $component): self
    {
        return $this->xotherwise(static fn() => $component);
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

    /**
     * @template A
     * @template B
     *
     * @param self<A, B>|Provider<A, B> $component
     *
     * @return self<A, B>
     */
    private static function collapse(self|Provider $component): self
    {
        if ($component instanceof Provider) {
            return $component->toComponent();
        }

        return $component;
    }
}
