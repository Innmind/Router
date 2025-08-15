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
     * This should only be used with a lazy Sequence.
     *
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
        $response = Attempt::error(new \Exception); // todo use better exception

        return Component::of(
            static fn($request, $input) => $components
                ->map(static fn($component) => $component($request, $input))
                ->sink($response)
                ->until(
                    static fn($_, $result, $continuation) => $result->match(
                        static fn() => $continuation->stop($result),
                        static fn() => $continuation->continue($result),
                    ),
                )
                ->mapError(static fn($e) => $e), // todo distinguish between no component vs none matched
        );
    }
}
