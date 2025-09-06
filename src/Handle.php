<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Http\{
    ServerRequest,
    Response,
};
use Innmind\Immutable\{
    Attempt,
    Map,
};

final class Handle
{
    /**
     * @template I
     * @psalm-pure
     *
     * @param callable(ServerRequest, I): Attempt<Response> $handler
     *
     * @return Component<I, Response>
     */
    #[\NoDiscard]
    public static function via(callable $handler): Component
    {
        return Component::of(static fn($_, $input) => Attempt::result($input))->guard(
            static fn() => Component::of($handler),
        );
    }

    /**
     * @psalm-pure
     *
     * @param callable(...mixed): Attempt<Response> $handler
     *
     * @return Component<Map<string, mixed>, Response>
     */
    #[\NoDiscard]
    public static function of(callable $handler): Component
    {
        /** @var Component<Map<string, mixed>, Response> */
        return self::via(
            static function($request, Map $variables) use ($handler) {
                if (!$variables->contains('request')) {
                    $variables = ($variables)('request', $request);
                }

                $refl = new \ReflectionFunction(\Closure::fromCallable($handler));
                $args = [];

                foreach ($refl->getParameters() as $parameter) {
                    $name = $parameter->getName();

                    if (!$parameter->isOptional() && !$variables->contains($name)) {
                        /** @var Attempt<Response> */
                        return Attempt::error(new \RuntimeException(\sprintf(
                            'Missing argument %s',
                            $parameter->getName(),
                        )));
                    }

                    $args = $variables
                        ->get($name)
                        ->match(
                            static fn($value) => \array_merge(
                                $args,
                                [$name => $value],
                            ),
                            static fn() => $args,
                        );
                }

                return $handler(...$args);
            },
        );
    }
}
