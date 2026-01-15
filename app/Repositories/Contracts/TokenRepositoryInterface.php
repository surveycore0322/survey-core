<?php
namespace App\Repositories\Contracts;

interface TokenRepositoryInterface
{
    /**
     * @return array{tokenable_type:string, tokenable_id:int}|null
     */
    public function findTokenableByHash(string $tokenHash, string $type): ?array;

    public function storeToken(string $tokenHash, string $type, string $tokenableType, int $tokenableId, ?string $expiresAtIso = null): void;
}
