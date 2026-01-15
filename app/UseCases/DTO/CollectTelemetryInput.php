<?php
namespace App\UseCases\DTO;

final readonly class CollectTelemetryInput
{
    public function __construct(
        public string $eventName,
        public ?int $formId = null,
        public ?string $viewId = null, // UUID
        public ?string $participantToken = null,
        public ?string $clientTs = null, // ISO date-time
        public ?string $deviceType = null,
        public ?string $locale = null,
        public ?string $userAgent = null,
        public ?array $properties = null,
    ) {}
}

final readonly class CollectTelemetryOutput
{
    public function __construct(public bool $accepted = true) {}
}
