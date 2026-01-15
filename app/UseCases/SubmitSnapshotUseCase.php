<?php

namespace App\UseCases;

use App\Domain\Token\TokenType;
use App\Repositories\Contracts\FormRepositoryInterface;
use App\Repositories\Contracts\ParticipantRepositoryInterface;
use App\Repositories\Contracts\SnapshotRepositoryInterface;
use App\Repositories\Contracts\TokenRepositoryInterface;
use App\UseCases\Assemblers\SnapshotAssembler;
use App\UseCases\Assemblers\TokenAssembler;
use App\UseCases\DTO\SubmitSnapshotInput;
use App\UseCases\DTO\SubmitSnapshotOutput;
use App\UseCases\Exceptions\UseCaseException;
use App\Services\Scalarizer\ScalarizerInterface;
use Carbon\CarbonImmutable;

final class SubmitSnapshotUseCase
{
    public function __construct(
        private TokenRepositoryInterface $tokenRepo,
        private FormRepositoryInterface $formRepo,
        private ParticipantRepositoryInterface $participantRepo,
        private SnapshotRepositoryInterface $snapshotRepo,
        private TokenAssembler $tokenAssembler,
        private SnapshotAssembler $snapshotAssembler,
        private ScalarizerInterface $scalarizer,
    ) {}

    public function execute(SubmitSnapshotInput $in): SubmitSnapshotOutput
    {
        // 1) form解決（public_token -> forms.id）
        $formHash = $this->tokenAssembler->hashRaw($in->publicToken);
        $tokenable = $this->tokenRepo->findTokenableByHash($formHash, TokenType::FormPublic->value);

        if (!$tokenable || $tokenable['tokenable_type'] !== 'forms') {
            throw new UseCaseException('FORM_NOT_FOUND', 'Form not found.', [], 404);
        }
        $formId = (int)$tokenable['tokenable_id'];

        // 2) 質問index（id/type/required）
        $questionIndex = $this->formRepo->getQuestionIndexByFormId($formId);
        // 例: [['id'=>1,'type'=>'boolean','required'=>true], ...]

        $knownQuestionIds = [];
        $requiredIds = [];
        $qTypeMap = []; // question_id => type

        foreach ($questionIndex as $q) {
            $qid = (int)$q['id'];
            $knownQuestionIds[] = $qid;
            $qTypeMap[$qid] = (string)$q['type'];
            if ((bool)$q['required']) $requiredIds[] = $qid;
        }

        // 3) 提出answersを配列化
        $submitted = [];
        foreach ($in->answers as $a) {
            $submitted[] = [
                'question_id' => (int)$a->questionId,
                'value' => $a->value,
            ];
        }

        // 4) Invariant：question重複/存在/required充足
        $seen = [];
        foreach ($submitted as $a) {
            $qid = (int)$a['question_id'];

            if (isset($seen[$qid])) {
                throw new UseCaseException('DUPLICATE_QUESTION_IN_SUBMISSION', 'Duplicate question in submission.', ['question_id' => $qid], 400);
            }
            $seen[$qid] = true;

            if (!in_array($qid, $knownIds, true)) {
                throw new UseCaseException('QUESTION_NOT_FOUND', 'Question not found in form.', ['question_id' => $qid], 400);
            }
        }
        foreach ($requiredIds as $rid) {
            if (!isset($seen[$rid])) {
                throw new UseCaseException('REQUIRED_ANSWER_MISSING', 'Required answer missing.', ['question_id' => $rid], 400);
            }
        }

        // 5) participant解決（participant_tokenがあれば既存、無ければ新規）
        [$participantId, $participantTokenRaw] = $this->resolveParticipant($formId, $in->nickname, $in->participantToken);

        // 6) snapshot作成
        $submittedAt = CarbonImmutable::now()->toIso8601String();
        $snapshotId = $this->snapshotRepo->createSnapshot($formId, $participantId, $submittedAt);

        // 7) 保存直前：QuestionTypeに応じて value_scalar 生成（Assembler内で統一）
        $answerEntities = $this->snapshotAssembler->buildAnswerEntities($questionIndex, $submitted);
        $rows = $this->snapshotAssembler->toPersistRows($answerEntities);
        $this->snapshotRepo->createAnswers($snapshotId, $rows);

        return new SubmitSnapshotOutput(
            snapshotId: $snapshotId,
            participantToken: $participantTokenRaw,
            submittedAt: $submittedAt,
        );
    }

    /**
     * @return array{0:int,1:string} [participantId, participantTokenRaw]
     */
    private function resolveParticipant(int $formId, string $nickname, ?string $participantTokenRaw): array
    {
        // 既存トークンがある場合
        if ($participantTokenRaw) {
            $hash = $this->tokenAssembler->hashRaw($participantTokenRaw);
            $tokenable = $this->tokenRepo->findTokenableByHash($hash, TokenType::Participant->value);

            if (!$tokenable || $tokenable['tokenable_type'] !== 'participants') {
                throw new UseCaseException('TOKEN_INVALID', 'Participant token is invalid.', [], 400);
            }

            $participantId = (int)$tokenable['tokenable_id'];

            // formスコープ厳密化（推奨）
            $p = $this->participantRepo->findById($participantId);
            if (!$p) {
                throw new UseCaseException('TOKEN_INVALID', 'Participant not found.', [], 400);
            }
            // ここでは findById の戻りに form_id を含める運用にするとより安全
            // あるいは ParticipantRepository に findByIdAndFormId を追加推奨

            $this->participantRepo->updateNickname($participantId, $nickname);

            return [$participantId, $participantTokenRaw];
        }

        // 新規作成：TokenEntity発行→participants保存→tokens保存
        $token = $this->tokenAssembler->issue(TokenType::Participant, 'participants', 0); // tokenableIdは後で確定

        // participantsにhashを保存
        $participantId = $this->participantRepo->create($formId, $nickname, $token->tokenHash);

        // tokenableIdを確定させて tokens に保存（hashのみ）
        $this->tokenRepo->storeToken(
            tokenHash: $token->tokenHash,
            type: TokenType::Participant->value,
            tokenableType: 'participants',
            tokenableId: $participantId,
            expiresAtIso: null
        );

        return [$participantId, $token->rawToken];
    }
}
