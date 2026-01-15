<?php

namespace App\UseCases\Assemblers;

use App\Domain\Snapshot\AnswerEntity;
use App\Services\Scalarizer\ScalarizerInterface;

final readonly class SnapshotAssembler
{
    public function __construct(private ScalarizerInterface $scalarizer) {}

    /**
     * QuestionIndex（question_id=>type/required）を元に、保存用AnswerEntityを作る。
     * @param array<int, array{id:int,type:string,required:bool}> $questionIndexRows
     * @param array<int, array{question_id:int, value:mixed}> $submittedAnswers
     * @return AnswerEntity[]
     */
    public function buildAnswerEntities(array $questionIndexRows, array $submittedAnswers): array
    {
        $typeMap = [];
        foreach ($questionIndexRows as $q) {
            $typeMap[(int)$q['id']] = (string)$q['type'];
        }

        $entities = [];
        foreach ($submittedAnswers as $a) {
            $qid = (int)$a['question_id'];
            $value = $a['value'] ?? null;
            $qType = $typeMap[$qid] ?? 'text';

            $entities[] = new AnswerEntity(
                questionId: $qid,
                value: $value,
                valueType: $this->detectValueType($value),
                valueScalar: $this->scalarizer->scalarize($qType, $value),
            );
        }

        return $entities;
    }

    /**
     * SnapshotRepositoryInterface::createAnswers 用の row へ変換
     * @param AnswerEntity[] $answers
     * @return array<int, array{question_id:int, value:mixed, value_type:?string, value_scalar:?string}>
     */
    public function toPersistRows(array $answers): array
    {
        $rows = [];
        foreach ($answers as $a) {
            $rows[] = [
                'question_id' => $a->questionId,
                'value' => $a->value,
                'value_type' => $a->valueType,
                'value_scalar' => $a->valueScalar,
            ];
        }
        return $rows;
    }

    private function detectValueType(mixed $v): ?string
    {
        return match (true) {
            is_bool($v) => 'bool',
            is_int($v) || is_float($v) => 'number',
            is_string($v) => 'string',
            is_array($v) => 'array',
            is_object($v) => 'object',
            $v === null => 'null',
            default => null,
        };
    }
}
