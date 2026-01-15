<?php
namespace App\Services;

final class ResultAggregator
{
    /**
     * Attend MVP:
     * - 判定質問は「最初のboolean質問」を使う（最小実装）
     * - value_scalar(ATTEND/DECLINE) があれば優先
     */
    public function pickAttendQuestionId(array $questionIndex): ?int
    {
        // $questionIndex: array of ['id'=>int,'required'=>bool] だけだとtypeがないので、
        // 本番は "boolean質問" をRepoから取れるようにするのがベター。
        // MVPでは「先頭質問」を出欠質問とみなす、などのルールに固定してもOK。
        return $questionIndex[0]['id'] ?? null;
    }

    /**
     * @param array $latestSnapshots SnapshotRepositoryInterface::getLatestSnapshotsPerParticipant の戻り
     * @return array{attend:string[], absent:string[]}
     */
    public function aggregateAttend(array $latestSnapshots, int $attendQuestionId): array
    {
        $attend = [];
        $absent = [];

        foreach ($latestSnapshots as $row) {
            $nickname = $row['nickname'] ?? 'Anonymous';
            $answer = null;

            foreach ($row['answers'] as $a) {
                if ((int)$a['question_id'] === $attendQuestionId) {
                    $answer = $a;
                    break;
                }
            }

            if ($answer === null) {
                // 未回答は absent 扱い（方針固定）
                $absent[] = $nickname;
                continue;
            }

            // scalarがあれば優先
            $scalar = $answer['value_scalar'] ?? null;
            if (is_string($scalar)) {
                if (strtoupper($scalar) === 'ATTEND') $attend[] = $nickname;
                else $absent[] = $nickname;
                continue;
            }

            // boolean fallback
            $v = $answer['value'];
            if ($v === true || $v === 1 || $v === 'true') $attend[] = $nickname;
            else $absent[] = $nickname;
        }

        return ['attend' => $attend, 'absent' => $absent];
    }
}
