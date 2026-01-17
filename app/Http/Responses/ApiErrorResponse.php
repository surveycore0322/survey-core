<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

final class ApiErrorResponse
{
    public static function from(
        int $status,
        string $code,
        string $message = 'Error',
        array $details = [],
        ?string $traceId = null,
    ): JsonResponse {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => (object)$details, // 空でも {} を保証
            ],
            'trace_id' => $traceId,
        ], $status);
    }
}
