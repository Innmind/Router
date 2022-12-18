<?php
declare(strict_types = 1);

namespace Tests\Innmind\Router\Route;

use Innmind\Router\{
    Route\Name,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class NameTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(Set\Strings::any()->filter(static fn($value) => $value !== ''))
            ->then(function(string $value): void {
                $this->assertSame($value, Name::of($value)->toString());
            });
    }

    public function testEquals()
    {
        $this
            ->forAll(
                Set\Strings::any()->filter(static fn($value) => $value !== ''),
                Set\Strings::any()->filter(static fn($value) => $value !== ''),
            )
            ->filter(static function($a, $b): bool {
                return $a !== $b;
            })
            ->then(function($a, $b): void {
                $this->assertTrue(Name::of($a)->equals(Name::of($a)));
                $this->assertFalse(Name::of($a)->equals(Name::of($b)));
                $this->assertFalse(Name::of($b)->equals(Name::of($a)));
            });
    }

    public function testThrowWhenEmptyName()
    {
        $this->expectException(DomainException::class);

        Name::of('');
    }
}
