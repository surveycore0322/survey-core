<?php

namespace App\UseCases\Assemblers;

use App\Domain\Telemetry\TelemetryEventEntity;
use App\Domain\Telemetry\TelemetryEventName;
use Carbon\CarbonImmutable;

final readonly class TelemetryAssembler
{
    /**
     * @param array{
     *  event_name:string, form_id?:?int, view_id?:?string, participant_token_hash?:?string,
     *  client_ts?:?string, device_type?:?string, locale?:?string, user_agent?:?string, properties?:?array
     * } $in
     */
    public function build(array $in): TelemetryEventEntity
    {
        return new TelemetryEventEntity(
            eventName: TelemetryEventName::fromString($in['event_name']),
            formId: $in['form_id'] ?? null,
            viewId: $in['view_id'] ?? null,
            participantTokenHash: $in['participant_token_hash'] ?? null,
            serverTs: CarbonImmutable::now()->toIso8601String(),
            clientTs: $in['client_ts'] ?? null,
            deviceType: $in['device_type'] ?? null,
            locale: $in['locale'] ?? null,
            userAgent: $in['user_agent'] ?? null,
            properties: $in['properties'] ?? null,
        );
    }

    /** Domain → repo row */
    public function toPersistRow(TelemetryEventEntity $e): array
    {
        return [
            'event_name' => $e->eventName->value,
            'form_id' => $e->formId,
            'view_id' => $e->viewId,
            'participant_token_hash' => $e->participantTokenHash,
            'server_ts' => $e->serverTs,
            'client_ts' => $e->clientTs,
            'device_type' => $e->deviceType,
            'locale' => $e->locale,
            'user_agent' => $e->userAgent,
            'properties' => $e->properties,
        ];
    }
}
