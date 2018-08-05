<?php
declare(strict_types = 1);

namespace Tests\Innmind\Router;

use Innmind\Router\{
    RequestMatcher,
    UrlGenerator,
    Route\Name,
};
use Innmind\Compose\ContainerBuilder\ContainerBuilder;
use Innmind\Url\{
    PathInterface,
    Path,
};
use Innmind\Immutable\{
    Map,
    Set,
};
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testLoad()
    {
        $container = (new ContainerBuilder)(
            new Path('container.yml'),
            (new Map('string', 'mixed'))
                ->put(
                    'routes',
                    Set::of(
                        PathInterface::class,
                        new Path('fixtures/routes1.yml'),
                        new Path('fixtures/routes2.yml')
                    )
                )
        );

        $requestMatcher = $container->get('requestMatcher');
        $urlGenerator = $container->get('urlGenerator');

        $this->assertInstanceOf(RequestMatcher::class, $requestMatcher);
        $this->assertInstanceOf(UrlGenerator::class, $urlGenerator);
        $this->assertSame('/foo', (string) $urlGenerator(new Name('foo')));
    }
}
