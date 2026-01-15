<?php

namespace App\Domain\Token;

enum TokenType: string
{
    case FormPublic = 'form_public';
    case Organizer = 'organizer';
    case Participant = 'participant';

    public static function fromString(string $value): self
    {
        $v = strtolower(trim($value));
        return match ($v) {
            'form_public' => self::FormPublic,
            'organizer' => self::Organizer,
            'participant' => self::Participant,
            default => throw new \InvalidArgumentException("Invalid token type: {$value}"),
        };
    }
}
