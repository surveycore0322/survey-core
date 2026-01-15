<?php

namespace App\Repositories\Eloquent;

use App\Models\Answer;
use App\Models\Snapshot;
use App\Repositories\Contracts\SnapshotRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class SnapshotRepository implements SnapshotRepositoryInterface
{
    public function createSnapshot(int $formId, int $participantId, string $submittedAtIso): int
    {
        $s = Snapshot::create([
            'form_id' => $formId,
            'participant_id' => $participantId,
            'submitted_at' => $submittedAtIso,
        ]);
        return (int)$s->id;
    }

    public function createAnswers(int $snapshotId, array $rows): void
    {
        foreach ($rows as $r) {
            Answer::create([
                'snapshot_id' => $snapshotId,
                'question_id' => $r['question_id'],
                'value' => $r['value'],
                'value_type' => $r['value_type'],
                'value_scalar' => $r['value_scalar'],
            ]);
        }
    }

    public function findLatestSnapshotIdsByFormId(int $formId): array
    {
        // participantごとの最新snapshot（idを採用。submitted_atでもOK）
        $rows = DB::table('snapshots')
            ->selectRaw('MAX(id) as latest_id')
            ->where('form_id', $formId)
            ->groupBy('participant_id')
            ->pluck('latest_id');

        return $rows->map(fn($v)=>(int)$v)->all();
    }

    public function findScalarAnswersForSnapshots(array $snapshotIds, int $questionId): array
    {
        if (count($snapshotIds) === 0) return [];

        // answers.value_scalar + participants.nickname
        return DB::table('answers as a')
            ->join('snapshots as s', 's.id', '=', 'a.snapshot_id')
            ->join('participants as p', 'p.id', '=', 's.participant_id')
            ->whereIn('a.snapshot_id', $snapshotIds)
            ->where('a.question_id', $questionId)
            ->select(['p.nickname as nickname', 'a.value_scalar as value_scalar'])
            ->get()
            ->map(fn($r)=>[
                'nickname' => (string)$r->nickname,
                'value_scalar' => $r->value_scalar !== null ? (string)$r->value_scalar : null,
            ])->all();
    }
}
