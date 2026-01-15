<?php

namespace App\Exceptions;

class InvalidAnswerException extends DomainException
{
    protected string $errorCode = 'INVALID_ANSWER';
    protected int $statusCode = 422;

    public function __construct(string $message = '回答内容に不正があります')
    {
        parent::__construct($message);
    }
}
