<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Immutable\Map;

final class Collect
{
    /**
     * @psalm-pure
     *
     * @param literal-string $name
     *
     * @return callable(mixed): Map<string, mixed>
     */
    public static function of(string $name): callable
    {
        return static fn($output) => Map::of([$name, $output]);
    }

    /**
     * @psalm-pure
     *
     * @param Component<Map<string, mixed>, Map<string, mixed>> $component
     *
     * @return Component<Map<string, mixed>, Map<string, mixed>>
     */
    public static function merge(Component $component): Component
    {
        /**
         * @psalm-suppress MixedArgumentTypeCoercion
         * @var Component<Map<string, mixed>, Map<string, mixed>>
         */
        return Component::of(
            static fn($request, Map $variables) => $component($request, $variables)->map(
                static fn($output) => $variables->merge($output),
            ),
        );
    }

    /**
     * @psalm-pure
     *
     * @param literal-string $name
     * @param Component<Map<string, mixed>, mixed> $component
     *
     * @return Component<Map<string, mixed>, Map<string, mixed>>
     */
    public static function as(string $name, Component $component): Component
    {
        /**
         * @psalm-suppress MixedArgumentTypeCoercion
         * @var Component<Map<string, mixed>, Map<string, mixed>>
         */
        return Component::of(
            static fn($request, Map $variables) => $component($request, $variables)->map(
                static fn($output) => ($variables)($name, $output),
            ),
        );
    }
}
