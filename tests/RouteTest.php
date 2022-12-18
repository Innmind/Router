<?php
declare(strict_types = 1);

namespace Tests\Innmind\Router;

use Innmind\Router\{
    Route,
    Route\Name,
};
use Innmind\UrlTemplate\Template;
use Innmind\Http\{
    Message\ServerRequest,
    Message\Method,
};
use Innmind\Url\Url;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testInterface()
    {
        $route = new Route(
            $name = new Name('foo'),
            $template = Template::of('/foo'),
            Method::post,
        );

        $this->assertSame($name, $route->name());
        $this->assertSame($template, $route->template());
    }

    public function testOf()
    {
        $route = Route::of(new Name('foo'), Method::post, Template::of('/foo/bar'));

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('foo', $route->name()->toString());
        $this->assertSame('/foo/bar', $route->template()->toString());
    }

    public function testMatches()
    {
        $route = Route::of(new Name('foo'), Method::post, Template::of('/foo{+bar}'));

        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->exactly(2))
            ->method('method')
            ->will($this->onConsecutiveCalls(
                Method::get,
                Method::post,
            ));
        $request
            ->expects($this->once())
            ->method('url')
            ->willReturn(Url::of('http://localhost:8000/foo/baz/bar'));

        $this->assertFalse($route->matches($request));
        $this->assertTrue($route->matches($request));
    }
}
