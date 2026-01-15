<?php

namespace App\Repositories\Contracts;

interface ParticipantRepositoryInterface
{
    public function create(int $formId, string $nickname, string $participantTokenHash): int;

    /** @return array{id:int,form_id:int,nickname:string}|null */
    public function findById(int $participantId): ?array;

    /** ★ 追加：formスコープ厳密化 */
    /** @return array{id:int,form_id:int,nickname:string}|null */
    public function findByIdAndFormId(int $participantId, int $formId): ?array;

    public function updateNickname(int $participantId, string $nickname): void;
}
