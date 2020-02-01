<?php
declare(strict_types = 1);

namespace Innmind\Router\Route;

use Innmind\Router\Exception\DomainException;
use Innmind\Immutable\Str;

final class Name
{
    private string $value;

    public function __construct(string $value)
    {
        if (Str::of($value)->empty()) {
            throw new DomainException;
        }

        $this->value = $value;
    }

    public function equals(self $route): bool
    {
        return $this->value === $route->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
