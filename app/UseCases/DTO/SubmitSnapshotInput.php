<?php
namespace App\UseCases\DTO;

final readonly class SubmitSnapshotInput
{
    /** @param AnswerInput[] $answers */
    public function __construct(
        public string $publicToken,
        public string $nickname,
        public ?string $participantToken,
        public array $answers,
    ) {}
}
