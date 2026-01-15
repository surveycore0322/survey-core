<?php

namespace App\Domain\Telemetry;

final readonly class TelemetryEventEntity
{
    /** @param array<string, mixed>|null $properties */
    public function __construct(
        public TelemetryEventName $eventName,
        public ?int $formId,
        public ?string $viewId,
        public ?string $participantTokenHash,
        public string $serverTs,
        public ?string $clientTs,
        public ?string $deviceType,
        public ?string $locale,
        public ?string $userAgent,
        public ?array $properties,
    ) {}
}
