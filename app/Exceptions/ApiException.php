<?php

namespace App\Exceptions;

use RuntimeException;

class ApiException extends RuntimeException
{
    public function __construct(
        public readonly int $status,
        public readonly string $code,
        public readonly string $publicMessage = 'Error',
        public readonly array $details = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($publicMessage, 0, $previous);
    }
}
