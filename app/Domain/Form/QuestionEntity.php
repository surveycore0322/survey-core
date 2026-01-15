<?php

namespace App\Domain\Form;

final readonly class QuestionEntity
{
    /**
     * @param string[]|null $options
     */
    public function __construct(
        public int $id,
        public int $formId,
        public string $label,
        public string $type, // QuestionTypeはまずstringでOK（将来enum化可）
        public ?array $options,
        public bool $required,
        public int $sortOrder,
    ) {
        if ($this->label === '') {
            throw new \InvalidArgumentException('Question.label must not be empty.');
        }
        if ($this->sortOrder < 0) {
            throw new \InvalidArgumentException('Question.sortOrder must be >= 0.');
        }
        if ($this->type === '') {
            throw new \InvalidArgumentException('Question.type must not be empty.');
        }
    }
}
