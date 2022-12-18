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
use Innmind\Url\Url;
use Innmind\Immutable\{
    Set,
    Str,
};
use PHPUnit\Framework\TestCase;

class RequestMatcherTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            RequestMatcherInterface::class,
            new RequestMatcher(Set::of(Route::class)),
        );
    }

    public function testInvokation()
    {
        $match = new RequestMatcher(
            Set::of(
                Route::of(new Name('baz'), Str::of('DELETE /foo')),
                $route = Route::of(new Name('foo'), Str::of('POST /foo')),
                Route::of(new Name('bar'), Str::of('GET /foo')),
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
            Set::of(
                Route::of(new Name('baz'), Str::of('DELETE /foo')),
                Route::of(new Name('foo'), Str::of('POST /foo')),
                Route::of(new Name('bar'), Str::of('GET /foo')),
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
