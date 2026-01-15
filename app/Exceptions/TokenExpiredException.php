<?php

namespace App\Exceptions;

class TokenExpiredException extends DomainException
{
    protected string $errorCode = 'TOKEN_EXPIRED';
    protected int $statusCode = 410;

    public function __construct()
    {
        parent::__construct('このイベントはすでに終了しています');
    }
}
