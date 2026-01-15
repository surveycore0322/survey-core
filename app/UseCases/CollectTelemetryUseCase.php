<?php

namespace App\UseCases;

use App\Repositories\Contracts\TelemetryRepositoryInterface;
use App\Services\TokenService;
use App\UseCases\Assemblers\TelemetryAssembler;
use App\UseCases\DTO\CollectTelemetryInput;
use App\UseCases\DTO\CollectTelemetryOutput;

final class CollectTelemetryUseCase
{
    public function __construct(
        private TelemetryRepositoryInterface $telemetryRepo,
        private TokenService $tokenService,
        private TelemetryAssembler $telemetryAssembler,
    ) {}

    public function execute(CollectTelemetryInput $in): CollectTelemetryOutput
    {
        $participantHash = null;
        if ($in->participantToken) {
            $participantHash = $this->tokenService->hash($in->participantToken);
        }

        $event = $this->telemetryAssembler->build([
            'event_name' => $in->eventName,
            'form_id' => $in->formId,
            'view_id' => $in->viewId,
            'participant_token_hash' => $participantHash,
            'client_ts' => $in->clientTs,
            'device_type' => $in->deviceType,
            'locale' => $in->locale,
            'user_agent' => $in->userAgent,
            'properties' => $in->properties,
        ]);

        $this->telemetryRepo->store($this->telemetryAssembler->toPersistRow($event));

        return new CollectTelemetryOutput(true);
    }
}
