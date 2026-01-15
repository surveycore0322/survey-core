<?php
namespace App\UseCases\DTO;

final readonly class QuestionDTO
{
    public function __construct(
        public int $id,
        public string $label,
        public string $type,
        public ?array $options,
        public bool $required,
        public int $sortOrder,
    ) {}
}
