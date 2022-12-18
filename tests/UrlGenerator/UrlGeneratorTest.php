<?php
declare(strict_types = 1);

namespace Tests\Innmind\Router\UrlGenerator;

use Innmind\Router\{
    UrlGenerator\UrlGenerator,
    UrlGenerator as UrlGeneratorInterface,
    Route,
    Route\Name,
};
use Innmind\Http\Message\Method;
use Innmind\UrlTemplate\Template;
use Innmind\Url\Url;
use Innmind\Immutable\{
    Sequence,
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
            new UrlGenerator(Sequence::of()),
        );
    }

    public function testInvokation()
    {
        $generate = new UrlGenerator(
            Sequence::of(
                Route::of(new Name('create'), Method::post, Template::of('/resource')),
                Route::of(new Name('list'), Method::get, Template::of('/resource')),
                Route::of(new Name('read'), Method::get, Template::of('/resource/{id}')),
                Route::of(new Name('update'), Method::put, Template::of('/resource/{id}')),
                Route::of(new Name('delete'), Method::delete, Template::of('/resource/{id}')),
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
