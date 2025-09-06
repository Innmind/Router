# Changelog

## [Unreleased]

### Fixed

- `Any::from()` was erasing guarded errors

## 5.1.0 - 2025-09-06

### Added

- `Innmind\Router\Component\Provider`
- `Innmind\Router\Pipe`
- `Innmind\Router\Component::guard()`
- `Innmind\Router\Component::feed()`
- `Innmind\Router\Component::xotherwise()`
- `Innmind\Router\Component::xor()`

### Fixed

- Add missing return type for `Respond::withHttpErrors()`

## 5.0.1 - 2025-08-31

### Fixes

- Add missing return type for `Router::__invoke()`
- Use the same input type from components for the one produced by `Any`

## 5.0.0 - 2025-08-15

### Added

- `Innmind\Router\Any`
- `Innmind\Router\Collect`
- `Innmind\Router\Component`
- `Innmind\Router\Endpoint`
- `Innmind\Router\Handle`
- `Innmind\Router\Host`
- `Innmind\Router\Method`
- `Innmind\Router\Respond`
- `Innmind\Router\Router`

### Changed

- Requires `innmind/immutable:~5.18`
- Requires `innmind/http:~8.0`
- `Innmind\Router\Route` is now an interface to provide a named route to `Innmind\Router\Endpoint::of()`

### Removed

- `Innmind\Router\RequestMatcher`
- `Innmind\Router\RequestMatcher\RequestMatcher`
- `Innmind\Router\UrlGenerator`
- `Innmind\Router\UrlGenerator\UrlGenerator`
- `Innmind\Router\Route\Name`
- `Innmind\Router\Route\Variables`
- `Innmind\Router\Under`

### Fixed

- PHP `8.4` deprecations

## 4.1.0 - 2023-11-26

### Added

- `Innmind\Router\Under`
    - can be used in `Innmind\Router\RequestMatcher\RequestMatcher`
    - can be used in `Innmind\Router\UrlGenerator\UrlGenerator`

## 4.0.0 - 2023-11-01

### Changed

- Requires `innmind/http:~7.0`

## 3.4.0 - 2023-09-17

### Added

- Support for `innmind/immutable:~5.0`

### Removed

- Support for PHP `8.1`

## 3.3.0 - 2023-01-29

### Added

- Support for `innmind/http:~6.0`

## 3.2.0 - 2023-01-01

### Added

- `Innmind\Router\Route\Variables::maybe(): Innmind\Immutable\Maybe<string>`

## 3.1.0 - 2022-12-29

### Added

- `Innmind\Router\Route::literal()` named constructor
