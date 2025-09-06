<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Router\Component\Provider;
use Innmind\Http\Response;
use Innmind\Immutable\{
    Sequence,
    Attempt,
};

final class Any
{
    /**
     * @psalm-pure
     * @template T
     *
     * @param Component<T, Response>|Provider<T, Response> $a
     * @param Component<T, Response>|Provider<T, Response> $rest
     *
     * @return Component<T, Response>
     */
    #[\NoDiscard]
    public static function of(
        Component|Provider $a,
        Component|Provider ...$rest,
    ): Component {
        if ($a instanceof Provider) {
            /** @var Component<T, Response> */
            $a = $a->toComponent();
        }

        foreach ($rest as $b) {
            $a = $a->or($b);
        }

        return $a;
    }

    /**
     * @internal
     * @psalm-pure
     * @template T
     *
     * @param Sequence<Component<T, Response>|Provider<T, Response>> $components
     *
     * @return Component<T, Response>
     */
    #[\NoDiscard]
    public static function from(Sequence $components): Component
    {
        /** @var Attempt<Response> */
        $response = Attempt::error(new Exception\NoRouteProvided);

        return Component::of(
            static fn($request, $input) => $components
                ->map(static fn($component) => match (true) {
                    $component instanceof Provider => $component->toComponent(),
                    default => $component,
                })
                ->sink($response)
                ->until(
                    static function($_, $component, $continuation) use ($request, $input) {
                        /**
                         * @psalm-suppress MixedArgument
                         * @var Attempt<Response>
                         */
                        $result = $component($request, $input);

                        // Never try to recover from a handle error as it may
                        // lead to another handle being called
                        return $result->match(
                            static fn() => $continuation->stop($result),
                            static fn($e) => match (true) {
                                $e instanceof Exception\HandleError => $continuation->stop($result),
                                default => $continuation->continue($result),
                            },
                        );
                    },
                ),
        );
    }
}
