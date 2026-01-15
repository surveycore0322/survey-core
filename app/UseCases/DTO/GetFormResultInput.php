<?php
namespace App\UseCases\DTO;

final readonly class GetFormResultInput
{
    public function __construct(
        public string $publicToken,
        public ?string $from = null, // ISO date-time
        public ?string $to = null,   // ISO date-time
    ) {}
}
