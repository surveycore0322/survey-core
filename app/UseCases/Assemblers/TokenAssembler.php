<?php

namespace App\UseCases\Assemblers;

use App\Domain\Token\TokenEntity;
use App\Domain\Token\TokenType;
use App\Services\TokenService;

final readonly class TokenAssembler
{
    public function __construct(private TokenService $tokenService) {}

    public function issue(TokenType $type, string $tokenableType, int $tokenableId, ?string $expiresAtIso = null): TokenEntity
    {
        $prefix = match ($type) {
            TokenType::FormPublic => 'PUB',
            TokenType::Organizer => 'ORG',
            TokenType::Participant => 'PAR',
        };

        $raw = $this->tokenService->generateRawToken($prefix);
        $hash = $this->tokenService->hash($raw);

        return new TokenEntity(
            rawToken: $raw,
            tokenHash: $hash,
            type: $type,
            tokenableType: $tokenableType,
            tokenableId: $tokenableId,
            expiresAt: $expiresAtIso,
        );
    }

    public function hashRaw(string $raw): string
    {
        return $this->tokenService->hash($raw);
    }
}
