<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TelemetryEventRequest;
use App\UseCases\CollectTelemetryUseCase;
use App\UseCases\DTO\CollectTelemetryInput;
use App\UseCases\Exceptions\UseCaseException;
use Illuminate\Http\JsonResponse;

final class TelemetryController extends Controller
{
    public function __construct(
        private readonly CollectTelemetryUseCase $collectTelemetry,
    ) {}

    /**
     * POST /api/v1/telemetry
     * MVP方針：原則落とさない。レスポンスは 204 でもOK。
     */
    public function store(TelemetryEventRequest $request): JsonResponse
    {
        try {
            $dto = new CollectTelemetryInput(
                eventName: $request->string('event_name')->toString(),
                formId: $request->input('form_id'),
                viewId: $request->input('view_id'),
                participantToken: $request->input('participant_token'),
                clientTs: $request->input('client_ts'),
                deviceType: $request->input('device_type'),
                locale: $request->input('locale'),
                userAgent: $request->input('user_agent'),
                properties: $request->input('properties'),
            );

            $this->collectTelemetry->execute($dto);

            return response()->json(null, 204);

        } catch (UseCaseException $e) {
            // テレメトリは落とさない方針なら204に寄せても良いが、まずは400で返す
            return response()->json([
                'error' => [
                    'code' => $e->codeKey,
                    'message' => $e->getMessage(),
                    'detail' => $e->detail,
                ],
            ], 400);
        }
    }
}
