<?php

namespace App\Domain\Token;

final readonly class TokenEntity
{
    public function __construct(
        public string $rawToken,   // 外部に出すのはraw
        public string $tokenHash,  // 永続化はhash
        public TokenType $type,
        public string $tokenableType, // 'forms' / 'participants' / 'organizers'
        public int $tokenableId,
        public ?string $expiresAt = null,
    ) {
        if ($this->rawToken === '') {
            throw new \InvalidArgumentException('Token.rawToken must not be empty.');
        }
        if ($this->tokenHash === '') {
            throw new \InvalidArgumentException('Token.tokenHash must not be empty.');
        }
    }
}
