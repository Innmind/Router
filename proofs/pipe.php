<?php
declare(strict_types = 1);

use Innmind\Router\{
    Router,
    Pipe,
};
use Innmind\Http;
use Innmind\Url\Url;
use Innmind\Immutable\Attempt;
use Innmind\BlackBox\Set;

return static function() {
    yield proof(
        'Pipe->method()->endpoint()->handle()',
        given(
            Set::of(...Http\Method::cases()),
            Set::of(...Http\ProtocolVersion::cases()),
        ),
        static function($assert, $method, $protocolVersion) {
            $request = Http\ServerRequest::of(
                Url::of('/foo'),
                $method,
                $protocolVersion,
            );
            $expected = Http\Response::of(
                Http\Response\StatusCode::ok,
                $request->protocolVersion(),
            );
            $router = Router::of(
                Pipe::new()
                    ->{$method->name}()
                    ->endpoint('{/watev}')
                    ->handle(static function($in, $input) use ($assert, $request, $expected) {
                        $assert->same($request, $in);
                        $assert->same(
                            'foo',
                            $input
                                ->get('watev')
                                ->match(
                                    static fn($value) => $value,
                                    static fn() => null,
                                ),
                        );

                        return Attempt::result($expected);
                    }),
            );

            $assert->same(
                $expected,
                $router($request)->match(
                    static fn($response) => $response,
                    static fn($error) => $error,
                ),
            );
        },
    );

    yield proof(
        'Pipe->method()->endpoint()->spread()->handle()',
        given(
            Set::of(...Http\Method::cases()),
            Set::of(...Http\ProtocolVersion::cases()),
        ),
        static function($assert, $method, $protocolVersion) {
            $in = Http\ServerRequest::of(
                Url::of('/foo'),
                $method,
                $protocolVersion,
            );
            $expected = Http\Response::of(
                Http\Response\StatusCode::ok,
                $in->protocolVersion(),
            );
            $router = Router::of(
                Pipe::new()
                    ->{$method->name}()
                    ->endpoint('{/watev}')
                    ->spread()
                    ->handle(static function($request, $watev) use ($assert, $in, $expected) {
                        $assert->same($in, $request);
                        $assert->same(
                            'foo',
                            $watev,
                        );

                        return Attempt::result($expected);
                    }),
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
};
