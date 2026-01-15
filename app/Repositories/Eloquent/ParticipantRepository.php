<?php

namespace App\Repositories\Eloquent;

use App\Models\Participant;
use App\Repositories\Contracts\ParticipantRepositoryInterface;

final class ParticipantRepository implements ParticipantRepositoryInterface
{
    public function create(int $formId, string $nickname, string $participantTokenHash): int
    {
        $p = Participant::create([
            'form_id' => $formId,
            'nickname' => $nickname,
            'participant_token_hash' => $participantTokenHash,
        ]);
        return (int)$p->id;
    }

    public function findById(int $participantId): ?array
    {
        $p = Participant::query()->find($participantId, ['id','form_id','nickname']);
        return $p ? ['id'=>(int)$p->id,'form_id'=>(int)$p->form_id,'nickname'=>(string)$p->nickname] : null;
    }

    public function findByIdAndFormId(int $participantId, int $formId): ?array
    {
        $p = Participant::query()
            ->where('id', $participantId)
            ->where('form_id', $formId)
            ->first(['id','form_id','nickname']);

        return $p ? ['id'=>(int)$p->id,'form_id'=>(int)$p->form_id,'nickname'=>(string)$p->nickname] : null;
    }

    public function updateNickname(int $participantId, string $nickname): void
    {
        Participant::query()->where('id', $participantId)->update(['nickname' => $nickname]);
    }
}
