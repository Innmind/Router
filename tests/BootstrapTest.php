<?php
declare(strict_types = 1);

namespace Tests\Innmind\Router;

use function Innmind\Router\bootstrap;
use Innmind\Router\{
    RequestMatcher\RequestMatcher,
    UrlGenerator\UrlGenerator,
};
use Innmind\Url\Path;
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    public function testBootstrap()
    {
        $services = bootstrap(Set::of(Path::class));

        $this->assertInstanceOf(RequestMatcher::class, $services['requestMatcher']);
        $this->assertInstanceOf(UrlGenerator::class, $services['urlGenerator']);
    }
}
