<?php
declare(strict_types = 1);

namespace Innmind\Router\Exception;

/**
 * @internal
 */
final class HandleError extends \RuntimeException
{
    public function __construct(
        private \Throwable $error,
    ) {
    }

    public function unwrap(): \Throwable
    {
        return $this->error;
    }
}
