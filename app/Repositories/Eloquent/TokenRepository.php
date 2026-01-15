<?php
namespace App\Repositories\Eloquent;

use App\Models\Token;
use App\Repositories\Contracts\TokenRepositoryInterface;

final class TokenRepository implements TokenRepositoryInterface
{
    public function findTokenableByHash(string $tokenHash, string $type): ?array
    {
        $t = Token::query()
            ->where('token_hash', $tokenHash)
            ->where('type', $type)
            ->first();

        if (!$t) return null;

        return [
            'tokenable_type' => $t->tokenable_type, // 例：'forms' 'participants'
            'tokenable_id' => (int)$t->tokenable_id,
        ];
    }

    public function storeToken(string $tokenHash, string $type, string $tokenableType, int $tokenableId, ?string $expiresAtIso = null): void
    {
        Token::query()->create([
            'token_hash' => $tokenHash,
            'type' => $type,
            'tokenable_type' => $tokenableType,
            'tokenable_id' => $tokenableId,
            'expires_at' => $expiresAtIso,
        ]);
    }
}
