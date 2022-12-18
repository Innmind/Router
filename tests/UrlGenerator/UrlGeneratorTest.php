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
                Route::of(Method::post, Template::of('/resource'))->named(Name::of('create')),
                Route::of(Method::get, Template::of('/resource'))->named(Name::of('list')),
                Route::of(Method::get, Template::of('/resource/{id}'))->named(Name::of('read')),
                Route::of(Method::put, Template::of('/resource/{id}'))->named(Name::of('update')),
                Route::of(Method::delete, Template::of('/resource/{id}'))->named(Name::of('delete')),
            ),
        );

        $this->assertInstanceOf(Url::class, $generate(Name::of('create')));
        $this->assertSame(
            '/resource',
            $generate(Name::of('create'))->toString(),
        );
        $this->assertSame(
            '/resource',
            $generate(Name::of('list'))->toString(),
        );
        $this->assertSame(
            '/resource/ecdd5bdc-943e-4a4f-8d16-255892bcacaa',
            $generate(
                Name::of('read'),
                Map::of(['id', 'ecdd5bdc-943e-4a4f-8d16-255892bcacaa']),
            )->toString(),
        );
        $this->assertSame(
            '/resource/ecdd5bdc-943e-4a4f-8d16-255892bcacaa',
            $generate(
                Name::of('update'),
                Map::of(['id', 'ecdd5bdc-943e-4a4f-8d16-255892bcacaa']),
            )->toString(),
        );
        $this->assertSame(
            '/resource/ecdd5bdc-943e-4a4f-8d16-255892bcacaa',
            $generate(
                Name::of('delete'),
                Map::of(['id', 'ecdd5bdc-943e-4a4f-8d16-255892bcacaa']),
            )->toString(),
        );
    }
}
