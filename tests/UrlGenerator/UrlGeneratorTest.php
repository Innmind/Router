<?php
declare(strict_types = 1);

namespace Tests\Innmind\Router\UrlGenerator;

use Innmind\Router\{
    UrlGenerator\UrlGenerator,
    UrlGenerator as UrlGeneratorInterface,
    Route,
    Route\Name,
};
use Innmind\Url\Url;
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
            new UrlGenerator(Set::of(Route::class)),
        );
    }

    public function testInvokation()
    {
        $generate = new UrlGenerator(
            Set::of(
                Route::of(new Name('create'), Str::of('POST /resource')),
                Route::of(new Name('list'), Str::of('GET /resource')),
                Route::of(new Name('read'), Str::of('GET /resource/{id}')),
                Route::of(new Name('update'), Str::of('PUT /resource/{id}')),
                Route::of(new Name('delete'), Str::of('DELETE /resource/{id}')),
            ),
        );

        $this->assertInstanceOf(Url::class, $generate(new Name('create')));
        $this->assertSame(
            '/resource',
            $generate(new Name('create'))->toString(),
        );
        $this->assertSame(
            '/resource',
            $generate(new Name('list'))->toString(),
        );
        $this->assertSame(
            '/resource/ecdd5bdc-943e-4a4f-8d16-255892bcacaa',
            $generate(
                new Name('read'),
                Map::of(['id', 'ecdd5bdc-943e-4a4f-8d16-255892bcacaa']),
            )->toString(),
        );
        $this->assertSame(
            '/resource/ecdd5bdc-943e-4a4f-8d16-255892bcacaa',
            $generate(
                new Name('update'),
                Map::of(['id', 'ecdd5bdc-943e-4a4f-8d16-255892bcacaa']),
            )->toString(),
        );
        $this->assertSame(
            '/resource/ecdd5bdc-943e-4a4f-8d16-255892bcacaa',
            $generate(
                new Name('delete'),
                Map::of(['id', 'ecdd5bdc-943e-4a4f-8d16-255892bcacaa']),
            )->toString(),
        );
    }
}
