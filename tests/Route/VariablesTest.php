<?php
declare(strict_types = 1);

namespace Tests\Innmind\Router\Route;

use Innmind\Router\Route\Variables;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class VariablesTest extends TestCase
{
    use BlackBox;

    public function testMaybe()
    {
        $this
            ->forAll(
                Set\Strings::any(),
                Set\Strings::any(),
                Set\Strings::any(),
            )
            ->filter(static fn($key, $_, $unknown) => $key !== $unknown)
            ->then(function($key, $value, $unknown) {
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
