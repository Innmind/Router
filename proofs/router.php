<?php
declare(strict_types = 1);

use Innmind\Router\{
    Router,
    Handle,
    Method,
    Endpoint,
    Host,
    Any,
    Respond,
    Collect,
    Pipe,
};
use Innmind\Http;
use Innmind\Url\Url;
use Innmind\Immutable\{
    Attempt,
    Sequence,
    SideEffect,
};
use Innmind\BlackBox\Set;
use Fixtures\Innmind\Url\Url as FUrl;

return static function() {
    yield proof(
        'Handle::via()',
        given(
            FUrl::any(),
            Set::of(...Http\Method::cases()),
            Set::of(...Http\ProtocolVersion::cases()),
        ),
        static function($assert, $url, $method, $protocolVersion) {
            $request = Http\ServerRequest::of(
                $url,
                $method,
                $protocolVersion,
            );
            $expected = Http\Response::of(
                Http\Response\StatusCode::ok,
                $request->protocolVersion(),
            );
            $router = Router::of(Handle::via(
                static function($in, $input) use ($assert, $request, $expected) {
                    $assert
                        ->object($input)
                        ->instance(SideEffect::class);
                    $assert->same($request, $in);

                    return Attempt::result($expected);
                },
            ));

            $assert->same(
                $expected,
                $router($request)->unwrap(),
            );

            $expected = new Exception;
            $router = Router::of(Handle::via(
                static function($in, $input) use ($assert, $request, $expected) {
                    $assert
                        ->object($input)
                        ->instance(SideEffect::class);
                    $assert->same($request, $in);

                    return Attempt::error($expected);
                },
            ));

            $assert->same(
                $expected,
                $router($request)->match(
                    static fn() => null,
                    static fn($e) => $e,
                ),
            );
        },
    );

    yield proof(
        'Method::*',
        given(
            Set::of(...Http\Method::cases()),
            Set::of(...Http\Method::cases()),
        )->filter(static fn($a, $b) => $a !== $b),
        static function($assert, $method, $other) {
            $request = Http\ServerRequest::of(
                Url::of('/'),
                $method,
                Http\ProtocolVersion::v11,
            );
            $component = Method::{$method->name}();

            $assert->same(
                $method,
                $component($request, SideEffect::identity())->unwrap(),
            );

            $request = Http\ServerRequest::of(
                Url::of('/'),
                $other,
                Http\ProtocolVersion::v11,
            );

            $assert->object(
                $component($request, SideEffect::identity())->match(
                    static fn() => null,
                    static fn($e) => $e,
                ),
            );
        },
    );

    yield proof(
        'Endpoint::of()',
        given(
            Set::of(...Http\Method::cases()),
            Set::of(...Http\ProtocolVersion::cases()),
        ),
        static function($assert, $method, $protocolVersion) {
            $request = Http\ServerRequest::of(
                Url::of('http://whatever/hello/world'),
                $method,
                $protocolVersion,
            );
            $router = Router::of(Endpoint::of('/hello{/name}'));

            $result = $router($request)->unwrap();
            $assert->count(1, $result);
            $assert->same(
                'world',
                $result->get('name')->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );

            $router = Router::of(Endpoint::of('/'));

            $assert->object(
                $router($request)->match(
                    static fn() => null,
                    static fn($e) => $e,
                ),
            );
        },
    );

    yield proof(
        'Component->map()',
        given(Set::of(...Http\Method::cases())),
        static function($assert, $method) {
            $request = Http\ServerRequest::of(
                Url::of('/'),
                $method,
                Http\ProtocolVersion::v11,
            );
            $component = Method::{$method->name}()->map(
                static fn($method) => $method->name,
            );

            $assert->same(
                $method->name,
                $component($request, SideEffect::identity())->unwrap(),
            );
        },
    );

    yield proof(
        'Host::of()',
        given(
            Set::of(...Http\Method::cases()),
            Set::of(...Http\ProtocolVersion::cases()),
        ),
        static function($assert, $method, $protocolVersion) {
            $request = Http\ServerRequest::of(
                Url::of('http://foo:bar@example.com/hello/world'),
                $method,
                $protocolVersion,
            );
            $router = Router::of(Host::of('example{.tld}/'));

            $result = $router($request)->unwrap();
            $assert->count(1, $result);
            $assert->same(
                'com',
                $result->get('tld')->match(
                    static fn($value) => $value,
                    static fn() => null,
                ),
            );

            $router = Router::of(Host::of('example.fr/'));

            $assert->object(
                $router($request)->match(
                    static fn() => null,
                    static fn($e) => $e,
                ),
            );
        },
    );

    yield proof(
        'Any::of',
        given(
            Set::of(...Http\Method::cases()),
            Set::of(...Http\Method::cases()),
            Set::of(...Http\Method::cases()),
        )
            ->filter(static fn($a, $b) => $a !== $b)
            ->filter(static fn($a, $b, $c) => $a !== $c && $b !== $c),
        static function($assert, $method, $other, $third) {
            $request = Http\ServerRequest::of(
                Url::of('/'),
                $other,
                Http\ProtocolVersion::v11,
            );
            $component = Any::of(
                Method::{$method->name}(),
                Method::{$other->name}(),
            );

            $assert->same(
                $other,
                $component($request, SideEffect::identity())->unwrap(),
            );

            $request = Http\ServerRequest::of(
                Url::of('/'),
                $third,
                Http\ProtocolVersion::v11,
            );

            $assert->object(
                $component($request, SideEffect::identity())->match(
                    static fn() => null,
                    static fn($e) => $e,
                ),
            );
        },
    );

    yield proof(
        'Any::from() empty sequence always fail',
        given(
            FUrl::any(),
            Set::of(...Http\Method::cases()),
            Set::of(...Http\ProtocolVersion::cases()),
        ),
        static function($assert, $url, $method, $protocolVersion) {
            $request = Http\ServerRequest::of(
                $url,
                $method,
                $protocolVersion,
            );
            $router = Router::of(Any::from(Sequence::of()));

            $assert->object(
                $router($request)->match(
                    static fn() => null,
                    static fn($e) => $e,
                ),
            );
        },
    );

    yield proof(
        'Any::from()',
        given(
            FUrl::any(),
            Set::of(...Http\Method::cases()),
            Set::of(...Http\Method::cases()),
            Set::of(...Http\ProtocolVersion::cases()),
            Set::of(...Http\Response\StatusCode::cases()),
        )->filter(static fn($_, $a, $b) => $a !== $b),
        static function($assert, $url, $method, $wrongMethod, $protocolVersion, $status) {
            $request = Http\ServerRequest::of(
                $url,
                $method,
                $protocolVersion,
            );
            $router = Router::of(Any::from(Sequence::of(
                Method::{$wrongMethod->name}()->map(
                    static fn() => Http\Response::of(
                        Http\Response\StatusCode::ok,
                        $request->protocolVersion(),
                    ),
                ),
                Handle::via(static fn($request) => Attempt::result(Http\Response::of(
                    $status,
                    $request->protocolVersion(),
                ))),
            )));

            $assert->same(
                $request->protocolVersion(),
                $router($request)->match(
                    static fn($response) => $response->protocolVersion(),
                    static fn() => null,
                ),
            );

            $expected = new Exception;
            $router = Router::of(Any::from(Sequence::of(
                Method::{$wrongMethod->name}()->map(
                    static fn($request) => Http\Response::of(
                        Http\Response\StatusCode::ok,
                        $request->protocolVersion(),
                    ),
                ),
                Handle::via(static fn($request) => Attempt::error($expected)),
            )));

            $assert->same(
                $expected,
                $router($request)->match(
                    static fn() => null,
                    static fn($e) => $e,
                ),
            );
        },
    );

    yield proof(
        'Any::from() should not override user errors',
        given(
            Set::of(...Http\Method::cases()),
            Set::of(...Http\Method::cases()),
            Set::of(...Http\ProtocolVersion::cases()),
        ),
        static function($assert, $method, $other, $protocolVersion) {
            $in = Http\ServerRequest::of(
                Url::of('/foo'),
                $method,
                $protocolVersion,
            );
            $expected = new Exception;
            $router = Router::of(
                Any::from(Sequence::of(
                    Pipe::new()
                        ->{$method->name}()
                        ->handle(static fn() => Attempt::error($expected)),
                    Pipe::new()
                        ->{$other->name}()
                        ->handle(static fn() => Attempt::error(new Exception)),
                )),
            );

            $assert->same(
                $expected,
                $router($in)->match(
                    static fn($response) => $response,
                    static fn($error) => $error,
                ),
            );

            $router = Router::of(
                Any::from(Sequence::of(
                    Pipe::new()
                        ->{$method->name}()
                        ->handle(static fn() => Attempt::error($expected)),
                    Pipe::new()
                        ->{$other->name}()
                        ->handle(static fn() => Attempt::error(new Exception)),
                )),
            );

            $assert->same(
                $expected,
                $router($in)->match(
                    static fn($response) => $response,
                    static fn($error) => $error,
                ),
            );
        },
    );

    yield proof(
        'Respond::with()',
        given(
            Set::of(...Http\Method::cases()),
            Set::of(...Http\ProtocolVersion::cases()),
            Set::of(...Http\Response\StatusCode::cases()),
        ),
        static function($assert, $method, $protocolVersion, $status) {
            $request = Http\ServerRequest::of(
                Url::of('/'),
                $method,
                $protocolVersion,
            );

            $router = Router::of(
                Any::from(Sequence::of(
                    Endpoint::of('/foo')->map(
                        static fn() => Http\Response::of(
                            Http\Response\StatusCode::ok,
                            $request->protocolVersion(),
                        ),
                    ),
                    Endpoint::of('/bar')->map(
                        static fn() => Http\Response::of(
                            Http\Response\StatusCode::ok,
                            $request->protocolVersion(),
                        ),
                    ),
                ))
                    ->or(Respond::notFound()),
            );

            $assert->same(
                Http\Response\StatusCode::notFound,
                $router($request)->match(
                    static fn($response) => $response->statusCode(),
                    static fn() => null,
                ),
            );
        },
    );

    yield proof(
        'Collect',
        given(
            Set::of(...Http\Method::cases()),
            Set::of(...Http\ProtocolVersion::cases()),
            Set::of(...Http\Response\StatusCode::cases()),
        ),
        static function($assert, $method, $protocolVersion, $status) {
            $request = Http\ServerRequest::of(
                Url::of('/foo'),
                $method,
                $protocolVersion,
            );
            $expected = Http\Response::of(
                $status,
                $request->protocolVersion(),
            );
            $router = Router::of(
                Method::{$method->name}()
                    ->map(Collect::of('method'))
                    ->pipe(Collect::merge(Endpoint::of('{/name}')))
                    ->pipe(Handle::via(
                        static function($_, $input) use ($expected, $assert, $method) {
                            $assert->same(
                                $method,
                                $input->get('method')->match(
                                    static fn($value) => $value,
                                    static fn() => null,
                                ),
                            );
                            $assert->same(
                                'foo',
                                $input->get('name')->match(
                                    static fn($value) => $value,
                                    static fn() => null,
                                ),
                            );

                            return Attempt::result($expected);
                        },
                    )),
            );

            $assert->same(
                $expected,
                $router($request)->unwrap(),
            );

            $router = Router::of(
                Method::{$method->name}()
                    ->map(Collect::of('method'))
                    ->pipe(Collect::as('params', Endpoint::of('{/name}')))
                    ->pipe(Handle::via(
                        static function($_, $input) use ($expected, $assert, $method) {
                            $assert->same(
                                $method,
                                $input->get('method')->match(
                                    static fn($value) => $value,
                                    static fn() => null,
                                ),
                            );
                            $assert->same(
                                'foo',
                                $input
                                    ->get('params')
                                    ->flatMap(static fn($params) => $params->get('name'))
                                    ->match(
                                        static fn($value) => $value,
                                        static fn() => null,
                                    ),
                            );

                            return Attempt::result($expected);
                        },
                    )),
            );

            $assert->same(
                $expected,
                $router($request)->unwrap(),
            );
        },
    );

    yield proof(
        'Handle::of()',
        given(
            Set::of(...Http\Method::cases()),
            Set::of(...Http\ProtocolVersion::cases()),
            Set::of(...Http\Response\StatusCode::cases()),
        ),
        static function($assert, $method, $protocolVersion, $status) {
            $req = Http\ServerRequest::of(
                Url::of('/foo'),
                $method,
                $protocolVersion,
            );
            $expected = Http\Response::of(
                $status,
                $req->protocolVersion(),
            );
            $router = Router::of(
                Method::{$method->name}()
                    ->map(Collect::of('method2'))
                    ->pipe(Collect::merge(Endpoint::of('{/name}')))
                    ->pipe(Handle::of(
                        static function($method2, $name, $request, $unknown = null) use ($req, $expected, $assert, $method) {
                            $assert->same($method, $method2);
                            $assert->same('foo', $name);
                            $assert->same($req, $request);
                            $assert->null($unknown);

                            return Attempt::result($expected);
                        },
                    )),
            );

            $assert->same(
                $expected,
                $router($req)->unwrap(),
            );
        },
    );

    yield proof(
        'Handle::of() with unknown argument fails',
        given(
            Set::of(...Http\Method::cases()),
            Set::of(...Http\ProtocolVersion::cases()),
            Set::of(...Http\Response\StatusCode::cases()),
        ),
        static function($assert, $method, $protocolVersion, $status) {
            $request = Http\ServerRequest::of(
                Url::of('/foo'),
                $method,
                $protocolVersion,
            );
            $expected = Http\Response::of(
                $status,
                $request->protocolVersion(),
            );
            $router = Router::of(
                Method::{$method->name}()
                    ->map(Collect::of('method2'))
                    ->pipe(Collect::merge(Endpoint::of('{/name}')))
                    ->pipe(Handle::of(
                        static function($method2, $name, $request, $unknown) use ($assert) {
                            $assert->fail('it should not call the handler');
                        },
                    )),
            );

            $assert->object(
                $expected,
                $router($request)->match(
                    static fn() => null,
                    static fn($e) => $e,
                ),
            );
        },
    );

    yield proof(
        'Respond::withHttpErrors() on method not allowed',
        given(
            FUrl::any(),
            Set::of(...Http\Method::cases()),
            Set::of(...Http\Method::cases()),
            Set::of(...Http\ProtocolVersion::cases()),
        )->filter(static fn($_, $a, $b) => $a !== $b),
        static function($assert, $url, $method, $other, $protocolVersion) {
            $request = Http\ServerRequest::of(
                $url,
                $method,
                $protocolVersion,
            );
            $router = Router::of(
                Method::{$other->name}()->otherwise(
                    Respond::withHttpErrors(),
                ),
            );

            $response = $router($request)->unwrap();

            $assert->same(
                Http\Response\StatusCode::methodNotAllowed,
                $response->statusCode(),
            );
        },
    );

    yield proof(
        'Respond::withHttpErrors() on path not found',
        given(
            FUrl::any(),
            Set::of(...Http\Method::cases()),
            Set::of(...Http\Method::cases()),
            Set::of(...Http\ProtocolVersion::cases()),
        )->filter(static fn($a) => $a->path()->toString() !== '/'),
        static function($assert, $url, $method, $other, $protocolVersion) {
            $request = Http\ServerRequest::of(
                $url,
                $method,
                $protocolVersion,
            );
            $router = Router::of(
                Endpoint::of('/')->otherwise(
                    Respond::withHttpErrors(),
                ),
            );

            $response = $router($request)->unwrap();

            $assert->same(
                Http\Response\StatusCode::notFound,
                $response->statusCode(),
            );
        },
    );
};
