<?php

namespace App\Domain\Telemetry;

enum TelemetryEventName: string
{
    case EventView = 'event_view';
    case FirstScroll = 'first_scroll';
    case AnswerSubmit = 'answer_submit';
    case AnswerSubmitFailed = 'answer_submit_failed';

    public static function fromString(string $value): self
    {
        $v = strtolower(trim($value));
        return match ($v) {
            'event_view' => self::EventView,
            'first_scroll' => self::FirstScroll,
            'answer_submit' => self::AnswerSubmit,
            'answer_submit_failed' => self::AnswerSubmitFailed,
            default => throw new \InvalidArgumentException("Invalid telemetry event name: {$value}"),
        };
    }
}
