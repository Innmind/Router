<?php
declare(strict_types = 1);

namespace Innmind\Router;

use Innmind\Http\ServerRequest;
use Innmind\Immutable\Maybe;

interface RequestMatcher
{
    /**
     * @return Maybe<Route>
     */
    public function __invoke(ServerRequest $request): Maybe;
}
