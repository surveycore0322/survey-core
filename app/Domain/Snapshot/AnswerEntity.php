<?php

namespace App\Domain\Snapshot;

final readonly class AnswerEntity
{
    public function __construct(
        public int $questionId,
        public mixed $value,       // 原本（JsonValue）
        public ?string $valueType, // "bool"/"string"/"number"/"array"...
        public ?string $valueScalar,
    ) {}
}
