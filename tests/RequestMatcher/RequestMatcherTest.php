<?php
declare(strict_types = 1);

namespace Tests\Innmind\Router\RequestMatcher;

use Innmind\Router\{
    RequestMatcher\RequestMatcher,
    RequestMatcher as RequestMatcherInterface,
    Route,
    Route\Name,
    Exception\NoMatchingRouteFound,
};
use Innmind\Http\Message\{
    ServerRequest,
    Method\Method,
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
            new RequestMatcher(Set::of(Route::class))
        );
    }

    public function testThrowWhenInvalidRouteSet()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type SetInterface<Innmind\Router\Route>');

        new RequestMatcher(Set::of('string'));
    }

    public function testInvokation()
    {
        $match = new RequestMatcher(
            Set::of(
                Route::class,
                Route::of(new Name('baz'), Str::of('DELETE /foo')),
                $route = Route::of(new Name('foo'), Str::of('POST /foo')),
                Route::of(new Name('bar'), Str::of('GET /foo'))
            )
        );
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->exactly(2))
            ->method('method')
            ->willReturn(Method::post());
        $request
            ->expects($this->once())
            ->method('url')
            ->willReturn(Url::fromString('/foo'));

        $this->assertSame($route, $match($request));
    }

    public function testThrowWhenNoMatchingRouteFound()
    {
        $match = new RequestMatcher(
            Set::of(
                Route::class,
                Route::of(new Name('baz'), Str::of('DELETE /foo')),
                Route::of(new Name('foo'), Str::of('POST /foo')),
                Route::of(new Name('bar'), Str::of('GET /foo'))
            )
        );
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->exactly(3))
            ->method('method')
            ->willReturn(Method::put());
        $request
            ->expects($this->never())
            ->method('url');

        $this->expectException(NoMatchingRouteFound::class);

        $match($request);
    }
}
