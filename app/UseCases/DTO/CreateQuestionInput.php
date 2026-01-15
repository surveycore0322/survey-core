<?php
namespace App\UseCases\DTO;

final readonly class CreateQuestionInput
{
    public function __construct(
        public string $label,
        public string $type,
        public bool $required = false,
        public int $sortOrder = 0,
        public ?array $options = null, // string[]
    ) {}
}
