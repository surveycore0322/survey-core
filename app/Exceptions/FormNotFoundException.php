<?php

namespace App\Exceptions;

class FormNotFoundException extends DomainException
{
    protected string $errorCode = 'FORM_NOT_FOUND';
    protected int $statusCode = 404;

    public function __construct()
    {
        parent::__construct('イベントが存在しません');
    }
}


