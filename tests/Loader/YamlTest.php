<?php
declare(strict_types = 1);

namespace Tests\Innmind\Router\Loader;

use Innmind\Router\{
    Loader\Yaml,
    Loader,
    Route,
    Exception\DomainException,
};
use Innmind\Url\Path;
use Innmind\Immutable\Set;
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;

class YamlTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Loader::class, new Yaml);
    }

    public function testInvokation()
    {
        $routes = (new Yaml)(
            Path::of('fixtures/routes1.yml'),
            Path::of('fixtures/routes2.yml'),
        );

        $this->assertInstanceOf(Set::class, $routes);
        $this->assertSame(Route::class, $routes->type());
        $this->assertCount(3, $routes);
        $routes = unwrap($routes);
        $this->assertSame('foo', (string) \current($routes)->name());
        \next($routes);
        $this->assertSame('bar', (string) \current($routes)->name());
        \next($routes);
        $this->assertSame('baz', (string) \current($routes)->name());
    }

    public function testFilesAreNotParsedWhenCallingTheParser()
    {
        // this call would throw if the file were to be parsed when parser called
        $this->assertInstanceOf(
            Set::class,
            (new Yaml)(Path::of('fixtures/invalidRouteName.yml')),
        );
    }

    public function testThrowWhenInvalidRouteName()
    {
        $this->expectException(DomainException::class);

        unwrap((new Yaml)(Path::of('fixtures/invalidRouteName.yml')));
    }

    public function testThrowWhenInvalidRouteTemplate()
    {
        $this->expectException(DomainException::class);

        unwrap((new Yaml)(Path::of('fixtures/invalidRouteTemplate.yml')));
    }
}
