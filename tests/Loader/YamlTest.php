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
use Innmind\Immutable\SetInterface;
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
            new Path('fixtures/routes1.yml'),
            new Path('fixtures/routes2.yml')
        );

        $this->assertInstanceOf(SetInterface::class, $routes);
        $this->assertSame(Route::class, (string) $routes->type());
        $this->assertCount(3, $routes);
        $this->assertSame('foo', (string) $routes->current()->name());
        $routes->next();
        $this->assertSame('bar', (string) $routes->current()->name());
        $routes->next();
        $this->assertSame('baz', (string) $routes->current()->name());
    }

    public function testThrowWhenInvalidRouteName()
    {
        $this->expectException(DomainException::class);

        (new Yaml)(new Path('fixtures/invalidRouteName.yml'));
    }

    public function testThrowWhenInvalidRouteTemplate()
    {
        $this->expectException(DomainException::class);

        (new Yaml)(new Path('fixtures/invalidRouteTemplate.yml'));
    }
}
