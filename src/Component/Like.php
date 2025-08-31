<?php
declare(strict_types = 1);

namespace Innmind\Router\Component;

use Innmind\Router\Component;

/**
 * @internal
 * @psalm-immutable
 * @template-covariant I
 * @template-covariant O
 */
trait Like
{
    /**
     * @template T
     *
     * @param callable(O): T $map
     *
     * @return Component<I, T>
     */
    #[\NoDiscard]
    public function map(callable $map): Component
    {
        /** @psalm-suppress InvalidArgument */
        return $this
            ->toComponent()
            ->map($map);
    }

    /**
     * @template T
     *
     * @param callable(O): (Component<O, T>|Provider<O, T>) $map
     *
     * @return Component<I, T>
     */
    #[\NoDiscard]
    public function flatMap(callable $map): Component
    {
        /** @psalm-suppress InvalidArgument */
        return $this
            ->toComponent()
            ->flatMap($map);
    }

    /**
     * @template T
     *
     * @param Component<O, T>|Provider<O, T> $component
     *
     * @return Component<I, T>
     */
    #[\NoDiscard]
    public function pipe(Component|Provider $component): Component
    {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        return $this
            ->toComponent()
            ->pipe($component);
    }

    /**
     * @template T
     *
     * @param callable(\Throwable): (Component<I, T>|Provider<I, T>) $recover
     *
     * @return Component<I, T>
     */
    #[\NoDiscard]
    public function otherwise(callable $recover): Component
    {
        return $this
            ->toComponent()
            ->otherwise($recover);
    }

    /**
     * @template T
     *
     * @param Component<I, T>|Provider<I, T> $component
     *
     * @return Component<I, T>
     */
    #[\NoDiscard]
    public function or(Component|Provider $component): Component
    {
        return $this
            ->toComponent()
            ->or($component);
    }

    /**
     * @param callable(\Throwable): \Throwable $map
     *
     * @return Component<I, O>
     */
    #[\NoDiscard]
    public function mapError(callable $map): Component
    {
        return $this
            ->toComponent()
            ->mapError($map);
    }
}
