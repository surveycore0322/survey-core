<?php

namespace App\Domain\Exceptions;

use RuntimeException;

final class InvariantViolationException extends RuntimeException
{
    public function __construct(
        public readonly string $invariantId, // e.g. INV-ATTEND-001
        public readonly array $context = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($invariantId, 0, $previous);
    }
}
