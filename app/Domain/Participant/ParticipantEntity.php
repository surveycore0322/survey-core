<?php

namespace App\Domain\Participant;

final readonly class ParticipantEntity
{
    public function __construct(
        public int $id,
        public int $formId,
        public string $nickname,
    ) {
        if (trim($this->nickname) === '') {
            throw new \InvalidArgumentException('Participant.nickname must not be empty.');
        }
    }
}
