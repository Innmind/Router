<?php
declare(strict_types = 1);

namespace Tests\Innmind\Router\RequestMatcher;

use Innmind\Router\{
    RequestMatcher\RequestMatcher,
    RequestMatcher as RequestMatcherInterface,
    Route,
    Route\Name,
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
}
