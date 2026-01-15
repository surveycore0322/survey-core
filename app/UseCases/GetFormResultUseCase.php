<?php

namespace App\UseCases;

use App\Domain\Token\TokenType;
use App\Repositories\Contracts\FormRepositoryInterface;
use App\Repositories\Contracts\SnapshotRepositoryInterface;
use App\Repositories\Contracts\TokenRepositoryInterface;
use App\UseCases\Assemblers\TokenAssembler;
use App\UseCases\DTO\GetFormResultInput;
use App\UseCases\DTO\GetFormResultOutput;
use App\UseCases\Exceptions\UseCaseException;

final class GetFormResultUseCase
{
    public function __construct(
        private TokenRepositoryInterface $tokenRepo,
        private FormRepositoryInterface $formRepo,
        private SnapshotRepositoryInterface $snapshotRepo,
        private TokenAssembler $tokenAssembler,
    ) {}

    public function execute(GetFormResultInput $in): GetFormResultOutput
    {
        // 1) public_token -> formId
        $hash = $this->tokenAssembler->hashRaw($in->publicToken);
        $tokenable = $this->tokenRepo->findTokenableByHash($hash, TokenType::FormPublic->value);

        if (!$tokenable || $tokenable['tokenable_type'] !== 'forms') {
            throw new UseCaseException('FORM_NOT_FOUND', 'Form not found.', [], 404);
        }
        $formId = (int)$tokenable['tokenable_id'];

        // 2) form本体
        $formRow = $this->formRepo->findFormById($formId);
        if (!$formRow) {
            throw new UseCaseException('FORM_NOT_FOUND', 'Form not found.', [], 404);
        }

        // 3) Attend判定質問ID（purpose優先）
        $qid = $this->formRepo->findAttendIntentQuestionId($formId);
        if (!$qid) {
            throw new UseCaseException('ATTEND_QUESTION_NOT_FOUND', 'Attend intent question not found.', [], 400);
        }

        // 4) 最新snapshotだけで集計（参加者ごとの最新提出）
        $latestSnapshotIds = $this->snapshotRepo->findLatestSnapshotIdsByFormId($formId);
        $rows = $this->snapshotRepo->findScalarAnswersForSnapshots($latestSnapshotIds, $qid);

        // 5) value_scalar 前提で分類（ATTEND/DECLINE）
        $attend = [];
        $absent = [];
        foreach ($rows as $r) {
            $scalar = $r['value_scalar'];
            if ($scalar === 'ATTEND') $attend[] = $r['nickname'];
            if ($scalar === 'DECLINE') $absent[] = $r['nickname'];
        }

        return new GetFormResultOutput(
            formId: $formId,
            title: $formRow['title'],
            eventDate: $formRow['event_date'],
            attend: $attend,
            absent: $absent,
        );
    }
}
