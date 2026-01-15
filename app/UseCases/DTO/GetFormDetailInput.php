<?php
namespace App\UseCases\DTO;

final readonly class GetFormDetailInput
{
    public function __construct(public string $publicToken) {}
}
