<?php
namespace App\UseCases\Exceptions;

use RuntimeException;

final class UseCaseException extends RuntimeException
{
    public function __construct(
        public readonly string $codeKey,
        string $message,
        public readonly array $detail = [],
        int $httpStatus = 400,
    ) {
        parent::__construct($message, $httpStatus);
    }
}
