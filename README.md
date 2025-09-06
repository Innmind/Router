# Router

[![codecov](https://codecov.io/gh/Innmind/Router/branch/develop/graph/badge.svg?branch=master)](https://codecov.io/gh/Innmind/Router)
[![Build Status](https://github.com/Innmind/Router/workflows/CI/badge.svg)](https://github.com/Innmind/Router/actions?query=workflow%3ACI)
[![Type Coverage](https://shepherd.dev/github/Innmind/Router/coverage.svg)](https://shepherd.dev/github/Innmind/Router)

Monadic HTTP router.

> [!NOTE]
> This package has been heavily inspired from [F# Giraffe](https://github.com/giraffe-fsharp/Giraffe).

## Installation

```sh
composer require innmind/router
```

## Usage

```php
use Innmind\Router\{
    Router,
    Any,
    Method,
    Endpoint,
    Handle,
};
use Innmind\Http\{
    ServerRequest,
    Response,
    Response\StatusCode,
};
use Innmind\Immutable\Attempt;

$router = Router::of(
    Any::of(
        Method::post()
            ->pipe(Endpoint::of('/url{/template}'))
            ->pipe(Handle::of(static fn(ServerRequest $request, string $template) => Attempt::result(Response::of(
                StatusCode::ok,
                $request->protocolVersion(),
            )))),
        Method::delete()
            ->pipe(Endpoint::of('/resource/{id}'))
            ->pipe(Handle::of(static fn(ServerRequest $request, string $id) => Attempt::result(Response::of(
                StatusCode::ok,
                $request->protocolVersion(),
            )))),
    ),
);

$response = $router(/* instance of ServerRequest */)->unwrap(); // Response
```

This example can be simplified as:

```php
use Innmind\Router\{
    Router,
    Pipe,
};
use Innmind\Http\{
    ServerRequest,
    Response,
    Response\StatusCode,
};
use Innmind\Immutable\Attempt;

$pipe = Pipe::new();
$router = Router::of(
    $pipe->any(
        $pipe
            ->post()
            ->endpoint('/url{/template}')
            ->spread()
            ->handle(static fn(ServerRequest $request, string $template) => Attempt::result(
                Response::of(
                    StatusCode::ok,
                    $request->protocolVersion(),
                ),
            )),
        $pipe
            ->delete()
            ->endpoint('/resource/{id}')
            ->spread()
            ->handle(static fn(ServerRequest $request, string $id) => Attempt::result(
                Response::of(
                    StatusCode::ok,
                    $request->protocolVersion(),
                ),
            )),
    ),
);

$response = $router(/* instance of ServerRequest */)->unwrap(); // Response
```

### Building a simple app

Example using the [`innmind/http-server`](https://github.com/Innmind/HttpServer/) package to respond with files stored in a private folder.

```php
use Innmind\Router\{
    Router,
    Any,
    Method,
    Endpoint,
    Handle,
};
use Innmind\HttpServer\Main;
use Innmind\OperatingSystem\OperatingSystem;
use Innmind\Http\{
    ServerRequest,
    Response,
    Response\StatusCode,
};
use Innmind\Filesystem\Name;
use Innmind\Url\Path;

new class extends Main {
    private Router $router;

    protected function preload(OperatingSystem $os): void
    {
        $this->router = Router::of(Any::of(
            Method::get()
                ->pipe(Endpoint::of('/image/{name}'))
                ->pipe(Handle::of(static fn(string $name) => self::loadFile(
                    $os,
                    $name,
                ))),
            Method::get()
                ->pipe(Endpoint::of('/image/random'))
                ->pipe(Handle::of(static fn() => self::loadFile(
                    $os,
                    generateRandomName(),
                ))),
        ));
    }

    protected function main(ServerRequest $request): Response
    {
        return ($this->router)($request)->match(
            static fn($response) => $response,
            static fn() => Response::of(
                StatusCode::notFound,
                $request->protocolVersion(),
            ),
        );
    }

    private function loadFile(OperatingSystem $os, string $name): Response
    {
        return $os
            ->filesystem()
            ->mount(Path::of('some/private/folder/'))
            ->unwrap()
            ->get(Name::of($name))
            ->match(
                static fn($file) => Response::of(
                    StatusCode::ok,
                    $request->protocolVersion(),
                    null,
                    $file->content(),
                ),
                static fn() => Response::of(
                    StatusCode::notFound,
                    $request->protocolVersion(),
                ),
            );
    }
}
```
