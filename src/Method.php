<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Http;
use Innmind\Immutable\Attempt;

final class Method
{
    /**
     * @return Component<mixed, Http\Method>
     */
    public static function get(): Component
    {
        return self::of(Http\Method::get);
    }

    /**
     * @return Component<mixed, Http\Method>
     */
    public static function post(): Component
    {
        return self::of(Http\Method::post);
    }

    /**
     * @return Component<mixed, Http\Method>
     */
    public static function put(): Component
    {
        return self::of(Http\Method::put);
    }

    /**
     * @return Component<mixed, Http\Method>
     */
    public static function patch(): Component
    {
        return self::of(Http\Method::patch);
    }

    /**
     * @return Component<mixed, Http\Method>
     */
    public static function delete(): Component
    {
        return self::of(Http\Method::delete);
    }

    /**
     * @return Component<mixed, Http\Method>
     */
    public static function options(): Component
    {
        return self::of(Http\Method::options);
    }

    /**
     * @return Component<mixed, Http\Method>
     */
    public static function trace(): Component
    {
        return self::of(Http\Method::trace);
    }

    /**
     * @return Component<mixed, Http\Method>
     */
    public static function connect(): Component
    {
        return self::of(Http\Method::connect);
    }

    /**
     * @return Component<mixed, Http\Method>
     */
    public static function head(): Component
    {
        return self::of(Http\Method::head);
    }

    /**
     * @return Component<mixed, Http\Method>
     */
    public static function link(): Component
    {
        return self::of(Http\Method::link);
    }

    /**
     * @return Component<mixed, Http\Method>
     */
    public static function unlink(): Component
    {
        return self::of(Http\Method::unlink);
    }

    /**
     * @return Component<mixed, Http\Method>
     */
    private static function of(Http\Method $method): Component
    {
        /**
         * @psalm-suppress PossiblyNullFunctionCall
         * @psalm-suppress MixedReturnStatement
         * @psalm-suppress InaccessibleMethod
         */
        return (\Closure::bind(
            static fn() => new Component(
                static fn(Http\ServerRequest $request, $input) => match ($request->method()) {
                    $method => Attempt::result($method),
                    default => Attempt::error(new \RuntimeException), // todo use better exception
                },
            ),
            null,
            Component::class,
        ))();
    }
}
