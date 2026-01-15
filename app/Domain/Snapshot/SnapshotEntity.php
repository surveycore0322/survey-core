<?php

namespace App\Domain\Snapshot;

final readonly class SnapshotEntity
{
    /** @param AnswerEntity[] $answers */
    public function __construct(
        public int $id,
        public int $formId,
        public int $participantId,
        public string $submittedAt, // ISO date-time
        public array $answers,
    ) {}
}
