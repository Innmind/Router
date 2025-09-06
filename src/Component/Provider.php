<?php
declare(strict_types = 1);

namespace Innmind\Router\Component;

use Innmind\Router\Component;

/**
 * @template-covariant I
 * @template-covariant O
 * @psalm-immutable
 */
interface Provider
{
    /**
     * @return Component<I, O>
     */
    #[\NoDiscard]
    public function toComponent(): Component;
}
