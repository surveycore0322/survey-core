<?php
namespace App\UseCases\DTO;

final readonly class CreateFormOutput
{
    public function __construct(
        public int $formId,
        public string $publicToken,
        public string $organizerToken,
        public string $publicUrl,
        public string $manageUrl,
    ) {}
}
