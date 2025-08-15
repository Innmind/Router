<?php
declare(strict_types = 1);

use Innmind\Router\{
    Router,
    Handle,
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
};
