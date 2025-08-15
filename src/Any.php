<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Http\Response;
use Innmind\Immutable\{
    Sequence,
    Attempt,
};

final class Any
{
    /**
     * @psalm-pure
     *
     * @param Component<mixed, Response> $a
     * @param Component<mixed, Response> $rest
     *
     * @return Component<mixed, Response>
     */
    public static function of(Component $a, Component ...$rest): Component
    {
        foreach ($rest as $b) {
            $a = $a->or($b);
        }

        return $a;
    }

    /**
     * @internal
     * @psalm-pure
     *
     * @param Sequence<Component<mixed, Response>> $components
     *
     * @return Component<mixed, Response>
     */
    public static function from(Sequence $components): Component
    {
        /** @var Attempt<Response> */
        $response = Attempt::error(new Exception\NoRouteProvided);

        return Component::of(
            static fn($request, $input) => $components
                ->sink($response)
                ->until(
                    static function($_, $component, $continuation) use ($request, $input) {
                        $result = $component($request, $input);

                        return $result->match(
                            static fn() => $continuation->stop($result),
                            static fn() => $continuation->continue($result),
                        );
                    },
                ),
        );
    }
}
