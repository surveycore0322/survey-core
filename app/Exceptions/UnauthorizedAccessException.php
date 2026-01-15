<?php

namespace App\Exceptions;

class UnauthorizedAccessException extends DomainException
{
    protected string $errorCode = 'UNAUTHORIZED_ACCESS';
    protected int $statusCode = 403;

    public function __construct()
    {
        parent::__construct('この操作を実行する権限がありません');
    }
}
