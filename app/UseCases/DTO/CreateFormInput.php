<?php
namespace App\UseCases\DTO;

final readonly class CreateFormInput
{
    /** @param CreateQuestionInput[]|null $questions */
    public function __construct(
        public string $organizerNickname,
        public string $title,
        public string $type,
        public ?string $eventDate = null, // ISO date or date-time
        public ?array $questions = null,
    ) {}
}
