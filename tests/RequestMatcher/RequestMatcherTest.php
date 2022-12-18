<?php
declare(strict_types = 1);

namespace Tests\Innmind\Router\RequestMatcher;

use Innmind\Router\{
    RequestMatcher\RequestMatcher,
    RequestMatcher as RequestMatcherInterface,
    Route,
    Route\Name,
};
use Innmind\Http\Message\{
    ServerRequest,
    Method,
};
use Innmind\UrlTemplate\Template;
use Innmind\Url\Url;
use Innmind\Immutable\Sequence;
use PHPUnit\Framework\TestCase;

class RequestMatcherTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            RequestMatcherInterface::class,
            new RequestMatcher(Sequence::of()),
        );
    }

    public function testInvokation()
    {
        $match = new RequestMatcher(
            Sequence::of(
                Route::of(Name::of('baz'), Method::delete, Template::of('/foo')),
                $route = Route::of(Name::of('foo'), Method::post, Template::of('/foo')),
                Route::of(Name::of('bar'), Method::get, Template::of('/foo')),
            ),
        );
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->exactly(2))
            ->method('method')
            ->willReturn(Method::post);
        $request
            ->expects($this->once())
            ->method('url')
            ->willReturn(Url::of('/foo'));

        $this->assertSame($route, $match($request)->match(
            static fn($route) => $route,
            static fn() => null,
        ));
    }

    public function testReturnNothingWhenNoMatchingRouteFound()
    {
        $match = new RequestMatcher(
            Sequence::of(
                Route::of(Name::of('baz'), Method::delete, Template::of('/foo')),
                Route::of(Name::of('foo'), Method::post, Template::of('/foo')),
                Route::of(Name::of('bar'), Method::get, Template::of('/foo')),
            ),
        );
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->exactly(3))
            ->method('method')
            ->willReturn(Method::put);
        $request
            ->expects($this->never())
            ->method('url');

        $this->assertNull($match($request)->match(
            static fn($route) => $route,
            static fn() => null,
        ));
    }
}
