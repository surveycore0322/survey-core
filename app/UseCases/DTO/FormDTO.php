<?php
namespace App\UseCases\DTO;

final readonly class FormDTO
{
    /** @param QuestionDTO[] $questions */
    public function __construct(
        public int $id,
        public string $title,
        public string $type,
        public ?string $eventDate,
        public array $questions,
    ) {}
}
