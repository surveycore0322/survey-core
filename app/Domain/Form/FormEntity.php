<?php

namespace App\Domain\Form;

final readonly class FormEntity
{
    /** @param QuestionEntity[] $questions */
    public function __construct(
        public int $id,
        public int $organizerId,
        public string $title,
        public FormType $type,
        public ?string $eventDate, // ISO date-time or date（プロジェクト方針で統一）
        public array $questions,
    ) {
        if (trim($this->title) === '') {
            throw new \InvalidArgumentException('Form.title must not be empty.');
        }
        // MVP: questionsは0でも許容（固定質問をUseCase側で注入する運用もあり得る）
    }

    /** @return int[] */
    public function requiredQuestionIds(): array
    {
        $ids = [];
        foreach ($this->questions as $q) {
            if ($q->required) $ids[] = $q->id;
        }
        return $ids;
    }

    /** @return array<int, string> question_id => question_type */
    public function questionTypeMap(): array
    {
        $map = [];
        foreach ($this->questions as $q) {
            $map[$q->id] = $q->type;
        }
        return $map;
    }
}
