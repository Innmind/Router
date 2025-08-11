<?php
declare(strict_types = 1);

namespace Tests\Innmind\Router\Route;

use Innmind\Router\{
    Route\Name,
    Exception\DomainException,
};
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
    PHPUnit\Framework\TestCase,
};

class NameTest extends TestCase
{
    use BlackBox;

    public function testInterface(): BlackBox\Proof
    {
        return $this
            ->forAll(Set::strings()->filter(static fn($value) => $value !== ''))
            ->prove(function(string $value): void {
                $this->assertSame($value, Name::of($value)->toString());
            });
    }

    public function testEquals(): BlackBox\Proof
    {
        return $this
            ->forAll(
                Set::strings()->filter(static fn($value) => $value !== ''),
                Set::strings()->filter(static fn($value) => $value !== ''),
            )
            ->filter(static function($a, $b): bool {
                return $a !== $b;
            })
            ->prove(function($a, $b): void {
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
