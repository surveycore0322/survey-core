<?php

namespace App\Exceptions;

use App\Http\Middleware\AttachTraceId;
use App\Http\Responses\ApiErrorResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        if ($this->shouldReturnJson($request)) {
            $mapper = app(ExceptionMapper::class);
            $m = $mapper->map($e);

            if ($m['report'] ?? false) {
                report($e);
            }

            $traceId = AttachTraceId::getFromRequest($request) ?? 'no-trace';

            // ★ ここが実行されているかを必ず残す（切り分け用）
            logger()->error('Handler reached (before api.error insert)', [
                'trace_id' => (string)$traceId,
                'path' => $request->getPathInfo(),
                'exception_class' => get_class($e),
                'exception_message' => $e->getMessage(),
            ]);

            // --- api.error を “必ず” audit_logs に残す（DB直INSERT） ---
            try {
                DB::connection('audit')->table('audit_logs')->insert([
                    'occurred_at' => now(),
                    'trace_id' => (string)$traceId,
                    'level' => 'error',
                    'event' => 'api.error',
                    'scope_type' => 'system',
                    'scope_id' => null,
                    'actor_type' => null,
                    'actor_id' => null,
                    'request_json' => json_encode([
                        'method' => $request->getMethod(),
                        'path' => $request->getPathInfo(),
                        'query' => $request->query(),
                        'ip' => $request->ip(),
                        'ua' => $request->userAgent(),
                        'expects_json' => $request->expectsJson(),
                    ], JSON_UNESCAPED_UNICODE),
                    'io_json' => json_encode([
                        'status' => $m['status'] ?? 500,
                    ], JSON_UNESCAPED_UNICODE),
                    'details_json' => json_encode([
                        'code' => $m['code'] ?? null,
                        'message' => $m['message'] ?? null,
                        'details' => $m['details'] ?? null,
                        'exception_class' => get_class($e),
                        'exception_message' => $e->getMessage(),
                        'report' => (bool)($m['report'] ?? false),
                    ], JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // ★ insert 成功もログに残す（切り分け用）
                logger()->error('Handler api.error insert OK', [
                    'trace_id' => (string)$traceId,
                ]);
            } catch (Throwable $logError) {
                // ★ 失敗理由を必ず laravel.log に出す
                logger()->error('Handler api.error insert FAILED', [
                    'trace_id' => (string)$traceId,
                    'error_class' => get_class($logError),
                    'error_message' => $logError->getMessage(),
                ]);
                report($logError);
            }
            // --- 追加ここまで ---

            return ApiErrorResponse::from(
                status: $m['status'],
                code: $m['code'],
                message: $m['message'],
                details: $m['details'],
                traceId: (string)$traceId,
            );
        }

        return parent::render($request, $e);
    }

    private function shouldReturnJson(Request $request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }
}
