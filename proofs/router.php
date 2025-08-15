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
            Set::of(...Http\ProtocolVersion::cases()),
            Set::of(...Http\Response\StatusCode::cases()),
        ),
        static function($assert, $url, $method, $protocolVersion, $status) {
            $request = Http\ServerRequest::of(
                $url,
                $method,
                $protocolVersion,
            );
            $router = Router::of(Any::from(Sequence::of(
                Handle::via(static fn() => Attempt::error(new Exception)),
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
                Handle::via(static fn() => Attempt::error(new Exception)),
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
        'Respond::with()',
        given(
            FUrl::any(),
            Set::of(...Http\Method::cases()),
            Set::of(...Http\ProtocolVersion::cases()),
            Set::of(...Http\Response\StatusCode::cases()),
        ),
        static function($assert, $url, $method, $protocolVersion, $status) {
            $request = Http\ServerRequest::of(
                $url,
                $method,
                $protocolVersion,
            );

            $router = Router::of(
                Any::from(Sequence::of(
                    Handle::via(static fn() => Attempt::error(new Exception)),
                    Handle::via(static fn($request) => Attempt::error(new Exception)),
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
};
