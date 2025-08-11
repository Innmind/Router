<?php
declare(strict_types = 1);

namespace Tests\Innmind\Router\Route;

use Innmind\Router\Route\Variables;
use Innmind\Immutable\Map;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
    PHPUnit\Framework\TestCase,
};

class VariablesTest extends TestCase
{
    use BlackBox;

    public function testMaybe(): BlackBox\Proof
    {
        return $this
            ->forAll(
                Set::strings(),
                Set::strings(),
                Set::strings(),
            )
            ->filter(static fn($key, $_, $unknown) => $key !== $unknown)
            ->prove(function($key, $value, $unknown) {
                $variables = Variables::of(Map::of([$key, $value]));

                $this->assertSame($value, $variables->maybe($key)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ));
                $this->assertNull($variables->maybe($unknown)->match(
                    static fn($value) => $value,
                    static fn() => null,
                ));
            });
    }
}
