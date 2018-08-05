<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Router\Route;
use Innmind\Http\Message\ServerRequest;

interface RequestMatcher
{
    public function __invoke(ServerRequest $request): Route;
}
