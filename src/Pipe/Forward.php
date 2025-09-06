<?php
declare(strict_types = 1);

namespace Innmind\Router\Pipe;

use Innmind\Router\Method;

/**
 * @psalm-immutable
 */
final class Forward
{
    private function __construct()
    {
    }

    /**
     * @internal
     * @psalm-pure
     */
    #[\NoDiscard]
    public static function new(): self
    {
        return new self;
    }

    #[\NoDiscard]
    public function get(): Forward\Method
    {
        return Forward\Method::of(Method::get());
    }

    #[\NoDiscard]
    public function post(): Forward\Method
    {
        return Forward\Method::of(Method::post());
    }

    #[\NoDiscard]
    public function put(): Forward\Method
    {
        return Forward\Method::of(Method::put());
    }

    #[\NoDiscard]
    public function patch(): Forward\Method
    {
        return Forward\Method::of(Method::patch());
    }

    #[\NoDiscard]
    public function delete(): Forward\Method
    {
        return Forward\Method::of(Method::delete());
    }

    #[\NoDiscard]
    public function options(): Forward\Method
    {
        return Forward\Method::of(Method::options());
    }

    #[\NoDiscard]
    public function trace(): Forward\Method
    {
        return Forward\Method::of(Method::trace());
    }

    #[\NoDiscard]
    public function connect(): Forward\Method
    {
        return Forward\Method::of(Method::connect());
    }

    #[\NoDiscard]
    public function head(): Forward\Method
    {
        return Forward\Method::of(Method::head());
    }

    #[\NoDiscard]
    public function link(): Forward\Method
    {
        return Forward\Method::of(Method::link());
    }

    #[\NoDiscard]
    public function unlink(): Forward\Method
    {
        return Forward\Method::of(Method::unlink());
    }
}
