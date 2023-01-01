<?php
declare(strict_types = 1);

namespace Innmind\Router\Route;

use Innmind\Router\Exception\LogicException;
use Innmind\Immutable\{
    Map,
    Maybe,
};

/**
 * @psalm-immutable
 */
final class Variables
{
    /** @var Map<string, string> */
    private Map $values;

    /**
     * @param Map<string, string> $values
     */
    private function __construct(Map $values)
    {
        $this->values = $values;
    }

    /**
     * @psalm-pure
     *
     * @param Map<string, string> $values
     */
    public static function of(Map $values): self
    {
        return new self($values);
    }

    /**
     * @param literal-string $name
     *
     * @throws LogicException When trying to access unknown template variable
     */
    public function get(string $name): string
    {
        return $this->values->get($name)->match(
            static fn($value) => $value,
            static fn() => throw new LogicException($name),
        );
    }

    /**
     * @return Maybe<string>
     */
    public function maybe(string $name): Maybe
    {
        return $this->values->get($name);
    }
}
