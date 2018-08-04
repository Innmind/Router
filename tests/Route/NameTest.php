<?php
declare(strict_types = 1);

namespace Tests\Innmind\Router\Route;

use Innmind\Router\{
    Route\Name,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Eris\{
    Generator,
    TestTrait,
};

class NameTest extends TestCase
{
    use TestTrait;

    public function testInterface()
    {
        $this
            ->forAll(Generator\string())
            ->when(static function(string $value): bool {
                return $value !== '';
            })
            ->then(function(string $value): void {
                $this->assertSame($value, (string) new Name($value));
            });
    }

    public function testThrowWhenEmptyName()
    {
        $this->expectException(DomainException::class);

        new Name('');
    }
}
