<?php
declare(strict_types = 1);

namespace Tests\Innmind\Router;

use Innmind\Router\{
    Route,
    Route\Name,
    Exception\LogicException,
};
use Innmind\UrlTemplate\Template;
use Innmind\Http\{
    ServerRequest,
    Method,
    Response,
    Response\StatusCode,
    ProtocolVersion,
};
use Innmind\Url\Url;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class RouteTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $route = Route::of(
            Method::post,
            $template = Template::of('/foo'),
        )->named($name = Name::of('foo'));

        $this->assertTrue($route->is($name));
        $this->assertFalse($route->is(Name::of('bat')));
        $this->assertSame($template, $route->template());
    }

    public function testOf()
    {
        $route = Route::of(Method::post, Template::of('/foo/bar'))->named(Name::of('foo'));

        $this->assertInstanceOf(Route::class, $route);
        $this->assertTrue($route->is(Name::of('foo')));
        $this->assertSame('/foo/bar', $route->template()->toString());
    }

    public function testMatches()
    {
        $route = Route::of(Method::post, Template::of('/foo{+bar}'));

        $request1 = ServerRequest::of(
            Url::of('http://localhost:8000/foo/baz/bar'),
            Method::get,
            ProtocolVersion::v11,
        );
        $request2 = ServerRequest::of(
            Url::of('http://localhost:8000/foo/baz/bar'),
            Method::post,
            ProtocolVersion::v11,
        );

        $this->assertFalse($route->matches($request1));
        $this->assertTrue($route->matches($request2));
    }

    public function testRespondToWithOkResponseByDefault()
    {
        $this
            ->forAll(Set\Elements::of(...ProtocolVersion::cases()))
            ->then(function($protocol) {
                $route = Route::of(Method::post, Template::of('/foo{+bar}'));
                $request = ServerRequest::of(
                    Url::of('/foo/somedata'),
                    Method::post,
                    $protocol,
                );

                $response = $route->respondTo($request);

                $this->assertSame(StatusCode::ok, $response->statusCode());
                $this->assertSame($protocol, $response->protocolVersion());
                $this->assertEmpty($response->body()->toString());
            });
    }

    public function testRespondTo()
    {
        $this
            ->forAll(Set\Elements::of(...ProtocolVersion::cases()))
            ->then(function($protocol) {
                $request = ServerRequest::of(
                    Url::of('/foo/somedata'),
                    Method::post,
                    $protocol,
                );
                $expected = Response::of(
                    StatusCode::ok,
                    $protocol,
                );
                $route = Route::of(Method::post, Template::of('/foo{+bar}'))->handle(
                    function($serverRequest, $variables) use ($request, $expected) {
                        $this->assertSame($request, $serverRequest);
                        $this->assertSame('/somedata', $variables->get('bar'));

                        return $expected;
                    },
                );

                $this->assertSame($expected, $route->respondTo($request));
            });
    }

    public function testLiteral()
    {
        $this
            ->forAll(
                Set\Elements::of(...Method::cases()),
                Set\Elements::of('/foo', '/bar', '/'),
                Set\Elements::of(...ProtocolVersion::cases()),
            )
            ->then(function($method, $url, $protocol) {
                $pattern = "{$method->toString()} $url";
                $route = Route::literal($pattern);

                $request = ServerRequest::of(
                    Url::of($url),
                    $method,
                    $protocol,
                );

                $this->assertTrue($route->matches($request));
            });

        $route = Route::literal('POST /foo');

        $request = ServerRequest::of(
            Url::of('/bar'),
            Method::post,
            ProtocolVersion::v11,
        );

        $this->assertFalse($route->matches($request));
    }

    public function testThrowWhenLiteralPatternIsInvalid()
    {
        $this->expectException(LogicException::class);

        Route::literal('foo /');
    }
}
