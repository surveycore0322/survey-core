<?php

namespace App\Domain\Form;

enum FormType: string
{
    case Attend = 'attend';
    case Survey = 'survey';
    case Vote = 'vote';
    case Memories = 'memories';

    public static function fromString(string $value): self
    {
        $v = strtolower(trim($value));
        return match ($v) {
            'attend' => self::Attend,
            'survey' => self::Survey,
            'vote' => self::Vote,
            'memories' => self::Memories,
            default => throw new \InvalidArgumentException("Invalid form type: {$value}"),
        };
    }
}
