<?php
declare(strict_types = 1);

namespace Tests\Innmind\Router\UrlGenerator;

use Innmind\Router\{
    UrlGenerator\UrlGenerator,
    UrlGenerator as UrlGeneratorInterface,
    Route,
    Route\Name,
};
use Innmind\Url\UrlInterface;
use Innmind\Immutable\{
    Set,
    Str,
    Map,
};
use PHPUnit\Framework\TestCase;

class UrlGeneratorTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            UrlGeneratorInterface::class,
            new UrlGenerator(Set::of(Route::class))
        );
    }

    public function testThrowWhenInvalidRouteSet()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type SetInterface<Innmind\Router\Route>');

        new UrlGenerator(Set::of('string'));
    }

    public function testInvokation()
    {
        $generate = new UrlGenerator(
            Set::of(
                Route::class,
                Route::of(new Name('create'), Str::of('POST /resource')),
                Route::of(new Name('list'), Str::of('GET /resource')),
                Route::of(new Name('read'), Str::of('GET /resource/{id}')),
                Route::of(new Name('update'), Str::of('PUT /resource/{id}')),
                Route::of(new Name('delete'), Str::of('DELETE /resource/{id}'))
            )
        );

        $this->assertInstanceOf(UrlInterface::class, $generate(new Name('create')));
        $this->assertSame(
            '/resource',
            (string) $generate(new Name('create'))
        );
        $this->assertSame(
            '/resource',
            (string) $generate(new Name('list'))
        );
        $this->assertSame(
            '/resource/ecdd5bdc-943e-4a4f-8d16-255892bcacaa',
            (string) $generate(
                new Name('read'),
                (new Map('string', 'variable'))
                    ->put('id', 'ecdd5bdc-943e-4a4f-8d16-255892bcacaa')
            )
        );
        $this->assertSame(
            '/resource/ecdd5bdc-943e-4a4f-8d16-255892bcacaa',
            (string) $generate(
                new Name('update'),
                (new Map('string', 'variable'))
                    ->put('id', 'ecdd5bdc-943e-4a4f-8d16-255892bcacaa')
            )
        );
        $this->assertSame(
            '/resource/ecdd5bdc-943e-4a4f-8d16-255892bcacaa',
            (string) $generate(
                new Name('delete'),
                (new Map('string', 'variable'))
                    ->put('id', 'ecdd5bdc-943e-4a4f-8d16-255892bcacaa')
            )
        );
    }
}
