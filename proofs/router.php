<?php
declare(strict_types = 1);

use Innmind\Router\{
    Router,
    Handle,
    Method,
    Endpoint,
};
use Innmind\Http;
use Innmind\Url\Url;
use Innmind\Immutable\{
    Attempt,
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
};
