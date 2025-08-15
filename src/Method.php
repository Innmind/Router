<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Http;
use Innmind\Immutable\Attempt;

final class Method
{
    /**
     * @psalm-pure
     *
     * @return Component<mixed, Http\Method>
     */
    public static function get(): Component
    {
        return self::of(Http\Method::get);
    }

    /**
     * @psalm-pure
     *
     * @return Component<mixed, Http\Method>
     */
    public static function post(): Component
    {
        return self::of(Http\Method::post);
    }

    /**
     * @psalm-pure
     *
     * @return Component<mixed, Http\Method>
     */
    public static function put(): Component
    {
        return self::of(Http\Method::put);
    }

    /**
     * @psalm-pure
     *
     * @return Component<mixed, Http\Method>
     */
    public static function patch(): Component
    {
        return self::of(Http\Method::patch);
    }

    /**
     * @psalm-pure
     *
     * @return Component<mixed, Http\Method>
     */
    public static function delete(): Component
    {
        return self::of(Http\Method::delete);
    }

    /**
     * @psalm-pure
     *
     * @return Component<mixed, Http\Method>
     */
    public static function options(): Component
    {
        return self::of(Http\Method::options);
    }

    /**
     * @psalm-pure
     *
     * @return Component<mixed, Http\Method>
     */
    public static function trace(): Component
    {
        return self::of(Http\Method::trace);
    }

    /**
     * @psalm-pure
     *
     * @return Component<mixed, Http\Method>
     */
    public static function connect(): Component
    {
        return self::of(Http\Method::connect);
    }

    /**
     * @psalm-pure
     *
     * @return Component<mixed, Http\Method>
     */
    public static function head(): Component
    {
        return self::of(Http\Method::head);
    }

    /**
     * @psalm-pure
     *
     * @return Component<mixed, Http\Method>
     */
    public static function link(): Component
    {
        return self::of(Http\Method::link);
    }

    /**
     * @psalm-pure
     *
     * @return Component<mixed, Http\Method>
     */
    public static function unlink(): Component
    {
        return self::of(Http\Method::unlink);
    }

    /**
     * @psalm-pure
     *
     * @return Component<mixed, Http\Method>
     */
    private static function of(Http\Method $method): Component
    {
        return Component::of(
            static fn(Http\ServerRequest $request, $input) => match ($request->method()) {
                $method => Attempt::result($method),
                default => Attempt::error(new \RuntimeException), // todo use better exception
            },
        );
    }
}
