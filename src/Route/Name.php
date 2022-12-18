<?php
declare(strict_types = 1);

namespace Innmind\Router\Route;

use Innmind\Router\Exception\DomainException;
use Innmind\Immutable\Str;

/**
 * @psalm-immutable
 */
final class Name
{
    private string $value;

    private function __construct(string $value)
    {
        if (Str::of($value)->empty()) {
            throw new DomainException;
        }

        $this->value = $value;
    }

    /**
     * @psalm-pure
     *
     * @param literal-string $value
     *
     * @throws DomainException
     */
    public static function of(string $value): self
    {
        return new self($value);
    }

    public function equals(self $route): bool
    {
        return $this->value === $route->value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
