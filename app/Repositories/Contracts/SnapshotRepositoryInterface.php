<?php

namespace App\Repositories\Contracts;

interface SnapshotRepositoryInterface
{
    public function createSnapshot(int $formId, int $participantId, string $submittedAtIso): int;

    /** @param array<int, array{question_id:int,value:mixed,value_type:?string,value_scalar:?string}> $rows */
    public function createAnswers(int $snapshotId, array $rows): void;

    /**
     * 最新提出の集計をSQLでやる前提：参加者ごとの最新snapshot_idを返す
     * @return int[] snapshot_id list
     */
    public function findLatestSnapshotIdsByFormId(int $formId): array;

    /**
     * snapshot_ids から、特定question_idの value_scalar と participant nickname を取得
     * @param int[] $snapshotIds
     * @return array<int, array{nickname:string, value_scalar:?string}>
     */
    public function findScalarAnswersForSnapshots(array $snapshotIds, int $questionId): array;
}
