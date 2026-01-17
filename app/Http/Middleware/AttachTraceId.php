<?php

namespace App\Http\Middleware;

use App\Infrastructure\Logging\AuditLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class AttachTraceId
{
    public const ATTR_KEY = 'trace_id';
    public const HEADER_KEY = 'X-Trace-Id';

    private const ATTR_START_NS = 'trace_start_ns';

    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $traceId = $request->header(self::HEADER_KEY) ?: (string) Str::uuid();
        $request->attributes->set(self::ATTR_KEY, $traceId);

        $request->attributes->set(self::ATTR_START_NS, hrtime(true));
        logger()->withContext(['trace_id' => $traceId]);

        try {
            /** @var Response $response */
            $response = $next($request);
        } catch (Throwable $e) {
            logger()->error('AttachTraceId caught', [
                'path' => $request->getPathInfo(),
                'trace_id' => $traceId,
                'class' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            // ★ ここで例外時ログを DB に書く
            $this->tryLogHttpException($request, $e);

            // ★ 元の例外をそのまま上に投げる
            throw $e;
        }

        $response->headers->set(self::HEADER_KEY, $traceId);

        // 成功時のIOログ（DB監査ログ）
        $this->tryLogHttpRequest($request, $response);

        return $response;
    }

    private function tryLogHttpRequest(Request $request, Response $response): void
    {
        // catch側でも参照できるよう先に確保
        $traceId = (string)($request->attributes->get(self::ATTR_KEY) ?? 'unknown');

        try {
            $startNs = (int)($request->attributes->get(self::ATTR_START_NS) ?? 0);
            $durationMs = $startNs > 0 ? (int)((hrtime(true) - $startNs) / 1_000_000) : null;

            $status = $response->getStatusCode();

            $base = [
                'scope_type' => 'system',
                'request' => $this->requestMeta($request),
                'io' => [
                    'status' => $status,
                    'duration_ms' => $durationMs,
                ],
            ];

            // ★重要：5xx は “例外相当” として error + http.exception を残す
            if ($status >= 500) {
                $this->auditLogger->error($traceId, 'http.exception', $base + [
                    'details' => [
                        'note' => 'response_status>=500 (exception may have been rendered before middleware catch)',
                    ],
                ]);
                return;
            }

            // それ以外は通常の http.request
            $this->auditLogger->info($traceId, 'http.request', $base);

        } catch (Throwable $logError) {
            // 監査ログの失敗理由は必ず出す（切り分けと運用のため）
            report($logError);

            logger()->error('audit_log_failed(http.request)', [
                'trace_id' => $traceId,
                'path' => $request->getPathInfo(),
                'error' => $logError->getMessage(),
                'class' => get_class($logError),
            ]);
        }
    }

    private function tryLogHttpException(Request $request, Throwable $e): void
    {
        try {
            $startNs = (int)($request->attributes->get(self::ATTR_START_NS) ?? 0);
            $durationMs = $startNs > 0 ? (int)((hrtime(true) - $startNs) / 1_000_000) : null;

            $traceId = (string)($request->attributes->get(self::ATTR_KEY) ?? 'unknown');

            $this->auditLogger->error($traceId, 'http.exception', [
                'scope_type' => 'system',
                'request' => $this->requestMeta($request),
                'io' => [
                    'duration_ms' => $durationMs,
                ],
                'details' => [
                    'exception_class' => get_class($e),
                    'message' => $e->getMessage(),
                ],
            ]);
        } catch (Throwable $logError) {
            logger()->error('audit_log_failed(http.exception)', [
                'trace_id' => $request->attributes->get(self::ATTR_KEY),
                'error' => $logError->getMessage(),
                'class' => get_class($logError),
            ]);
        }
    }

    private function requestMeta(Request $request): array
    {
        return [
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
            'query' => $request->query(),
            'ip' => $request->ip(),
            'ua' => $request->userAgent(),
            'expects_json' => $request->expectsJson(),
        ];
    }

    public static function getFromRequest(Request $request): ?string
    {
        return $request->attributes->get(self::ATTR_KEY);
    }
}
