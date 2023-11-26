<?php
declare(strict_types = 1);

namespace Tests\Innmind\Router\RequestMatcher;

use Innmind\Router\{
    RequestMatcher\RequestMatcher,
    RequestMatcher as RequestMatcherInterface,
    Route,
    Route\Name,
    Under,
};
use Innmind\Http\{
    ServerRequest,
    Method,
    ProtocolVersion,
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
                Route::of(Method::delete, Template::of('/foo')),
                $route = Route::of(Method::post, Template::of('/foo')),
                Route::of(Method::get, Template::of('/foo')),
            ),
        );
        $request = ServerRequest::of(
            Url::of('/foo'),
            Method::post,
            ProtocolVersion::v11,
        );

        $this->assertSame($route, $match($request)->match(
            static fn($route) => $route,
            static fn() => null,
        ));
    }

    public function testReturnNothingWhenNoMatchingRouteFound()
    {
        $match = new RequestMatcher(
            Sequence::of(
                Route::of(Method::delete, Template::of('/foo')),
                Route::of(Method::post, Template::of('/foo')),
                Route::of(Method::get, Template::of('/foo')),
            ),
        );
        $request = ServerRequest::of(
            Url::of('/'),
            Method::put,
            ProtocolVersion::v11,
        );

        $this->assertNull($match($request)->match(
            static fn($route) => $route,
            static fn() => null,
        ));
    }

    public function testMatchGroupedRoutes()
    {
        $match = new RequestMatcher(
            Sequence::of(
                Under::of(Template::of('/foo'))
                    ->route(Method::delete)
                    ->route(Method::post, static fn($route) => $route->named(Name::of('foo')))
                    ->route(Method::get),
                Under::of(Template::of('/bar'))
                    ->route(Method::delete)
                    ->route(Method::post, static fn($route) => $route->named(Name::of('bar')))
                    ->route(Method::get),
            ),
        );
        $foo = ServerRequest::of(
            Url::of('/foo'),
            Method::post,
            ProtocolVersion::v11,
        );
        $bar = ServerRequest::of(
            Url::of('/bar'),
            Method::post,
            ProtocolVersion::v11,
        );

        $this->assertTrue($match($foo)->match(
            static fn($route) => $route->is(Name::of('foo')),
            static fn() => null,
        ));
        $this->assertTrue($match($bar)->match(
            static fn($route) => $route->is(Name::of('bar')),
            static fn() => null,
        ));
    }

    public function testMatchNotAllowedMethod()
    {
        $match = new RequestMatcher(
            Sequence::of(
                Under::of(Template::of('/foo'))
                    ->route(Method::delete)
                    ->route(Method::post)
                    ->route(Method::get),
            ),
        );
        $request = ServerRequest::of(
            Url::of('/foo'),
            Method::head,
            ProtocolVersion::v11,
        );

        $response = $match($request)->match(
            static fn($route) => $route->respondTo($request),
            static fn() => null,
        );

        $this->assertNotNull($response);
        $this->assertSame(405, $response->statusCode()->toInt());
    }
}
