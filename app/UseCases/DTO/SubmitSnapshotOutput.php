<?php
namespace App\UseCases\DTO;

final readonly class SubmitSnapshotOutput
{
    public function __construct(
        public int $snapshotId,
        public string $participantToken,
        public string $submittedAt, // ISO date-time
    ) {}
}
