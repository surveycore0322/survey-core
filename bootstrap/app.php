<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Exceptions\ExceptionMapper;
use App\Http\Middleware\AttachTraceId;
use App\Http\Responses\ApiErrorResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('api', [
            AttachTraceId::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // API例外の統一レンダリング（Laravel 11+ / 12系の標準フロー）
        $exceptions->render(function (\Throwable $e, Request $request) {

            // API以外は Laravel 標準に任せる
            if (!($request->expectsJson() || $request->is('api/*'))) {
                return null;
            }

            /** @var ExceptionMapper $mapper */
            $mapper = app(ExceptionMapper::class);
            $m = $mapper->map($e);

            // trace_id は middleware で付与済みの想定（無ければ fallback）
            $traceId = AttachTraceId::getFromRequest($request) ?? 'no-trace';

            // report 判断（従来方針を尊重）
            if ($m['report'] ?? false) {
                report($e);
            }

            // ---- 監査ログに残す例外メッセージは sanitize（SQL/接続情報を残さない） ----
            $exceptionMessage = $e->getMessage();
            if ($e instanceof \Illuminate\Database\UniqueConstraintViolationException) {
                $exceptionMessage = 'Unique constraint violation';
            } elseif ($e instanceof \Illuminate\Database\QueryException) {
                $exceptionMessage = 'QueryException';
            }

            // ---- 監査ログ用の details（レスポンス用 details とは別） ----
            // まずは ExceptionMapper が作った details を引き継ぐ
            $logDetails = $m['details'] ?? null;

            // Unique制約違反のときだけ、監査ログには constraint を追加（レスポンスには出さない）
            if ($e instanceof \Illuminate\Database\QueryException) {
                $rawMsg = (string)($e->errorInfo[2] ?? $e->getMessage());
                $constraint = extractConstraintNameForAudit($rawMsg);

                if ($constraint !== null) {
                    if (!is_array($logDetails)) {
                        $logDetails = [];
                    }
                    $logDetails['constraint'] = $constraint;
                }
            }

            // ★ api.error を “必ず” audit_logs に残す（DB直INSERT）
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
                        'details' => $logDetails, // ← 監査ログ用（constraintを入れるのはここだけ）
                        'exception_class' => get_class($e),
                        'exception_message' => $exceptionMessage,
                        'report' => (bool)($m['report'] ?? false),
                    ], JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $logError) {
                // 監査ログ失敗でAPIレスポンスを壊さない
                report($logError);
            }

            // ---- レスポンス用 details からは constraint を除外（外向き最小化） ----
            $responseDetails = $m['details'] ?? null;
            if (is_array($responseDetails) && array_key_exists('constraint', $responseDetails)) {
                unset($responseDetails['constraint']);
            }

            return ApiErrorResponse::from(
                status: $m['status'],
                code: $m['code'],
                message: $m['message'],
                details: $responseDetails, // ← ここが外向き
                traceId: (string)$traceId,
            );
        });
    })
    ->create();

/**
 * 監査ログ用：DB例外メッセージから constraint 名を抽出（簡易・安全版）
 * - MySQL: "... for key 'table.constraint'"
 * - PostgreSQL: "... unique constraint "constraint""
 */
function extractConstraintNameForAudit(string $rawMsg): ?string
{
    // MySQL: for key 'table.constraint'
    if (preg_match("/for key '([^']+)'/i", $rawMsg, $m)) {
        return $m[1];
    }

    // PostgreSQL: unique constraint "constraint"
    if (preg_match('/unique constraint "([^"]+)"/i', $rawMsg, $m)) {
        return $m[1];
    }

    return null;
}
