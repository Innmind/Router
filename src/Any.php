<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Http\Response;

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
}
