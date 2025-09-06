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
            $a = $a->xor($b);
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
        $beacon = new \Exception;

        return Component::of(
            static fn($request, $input) => $components
                ->map(static fn($component) => match (true) {
                    $component instanceof Provider => $component->toComponent(),
                    default => $component,
                })
                ->sink($response)
                ->until(
                    static function($previous, $component, $continuation) use ($beacon, $request, $input) {
                        /**
                         * @psalm-suppress MixedArgument
                         * @var Attempt<Response>
                         */
                        $result = $previous
                            ->mapError(static fn() => $beacon)
                            ->xrecover(static fn() => $component($request, $input));

                        // If the new error is the beacon then it means the
                        // previous error was a guarded one and it will try to
                        // recover from it. So we can stop iterating over other
                        // components.
                        return $result->match(
                            static fn() => $continuation->stop($result),
                            static fn($e) => match ($e) {
                                $beacon => $continuation->stop($previous),
                                default => $continuation->continue($result),
                            },
                        );
                    },
                ),
        );
    }
}
