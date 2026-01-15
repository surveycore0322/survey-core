<?php
namespace App\Services;

use App\Repositories\Contracts\ParticipantRepositoryInterface;
use App\Repositories\Contracts\TokenRepositoryInterface;
use App\UseCases\Exceptions\UseCaseException;

final class ParticipantResolver
{
    public function __construct(
        private TokenService $tokenService,
        private TokenRepositoryInterface $tokenRepo,
        private ParticipantRepositoryInterface $participantRepo,
    ) {}

    /**
     * participant_token があれば既存participantを解決し、なければ作成する。
     * 返り値：['participant_id'=>int, 'participant_token'=>string]
     */
    public function resolveOrCreate(int $formId, string $nickname, ?string $participantTokenRaw): array
    {
        if ($participantTokenRaw) {
            $hash = $this->tokenService->hash($participantTokenRaw);
            $tokenable = $this->tokenRepo->findTokenableByHash($hash, 'participant');

            if (!$tokenable || $tokenable['tokenable_type'] !== 'participants') {
                throw new UseCaseException('TOKEN_INVALID', 'Participant token is invalid.');
            }

            $participantId = (int)$tokenable['tokenable_id'];

            // form単位を保証したい場合：participantsテーブルにform_idがある前提でチェックをrepoに追加しても良い
            $this->participantRepo->updateNickname($participantId, $nickname);

            return ['participant_id' => $participantId, 'participant_token' => $participantTokenRaw];
        }

        // 新規作成
        $raw = $this->tokenService->generateRawToken('PAR');
        $hash = $this->tokenService->hash($raw);

        $participantId = $this->participantRepo->create($formId, $nickname, $hash);

        // tokens に紐づけ（tokenable_typeはテーブル名で表現）
        $this->tokenRepo->storeToken($hash, 'participant', 'participants', $participantId);

        return ['participant_id' => $participantId, 'participant_token' => $raw];
    }
}
