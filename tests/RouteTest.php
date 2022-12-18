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
    Message\StatusCode,
    Message\Response,
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

    public function testRespondToWithOkResponseByDefault()
    {
        $this
            ->forAll(Set\Elements::of(...ProtocolVersion::cases()))
            ->then(function($protocol) {
                $route = Route::of(Method::post, Template::of('/foo{+bar}'));
                $request = $this->createMock(ServerRequest::class);
                $request
                    ->expects($this->once())
                    ->method('protocolVersion')
                    ->willReturn($protocol);
                $request
                    ->expects($this->once())
                    ->method('url')
                    ->willReturn(Url::of('/foo/somedata'));

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
                $request = $this->createMock(ServerRequest::class);
                $request
                    ->expects($this->once())
                    ->method('url')
                    ->willReturn(Url::of('/foo/somedata'));
                $expected = $this->createMock(Response::class);
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
}
