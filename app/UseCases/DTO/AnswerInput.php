<?php
namespace App\UseCases\DTO;

final readonly class AnswerInput
{
    public function __construct(
        public int $questionId,
        public mixed $value, // JsonValue
    ) {}
}
