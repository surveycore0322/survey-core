<?php

namespace App\Exceptions;

use Illuminate\Validation\ValidationException as LaravelValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use App\UseCases\Exceptions\UseCaseException;
use Throwable;

final class ExceptionMapper
{
    /**
     * @return array{status:int, code:string, message:string, details:array, report:bool}
     */
    public function map(Throwable $e): array
    {
        // 422: validation
        if ($e instanceof LaravelValidationException) {
            return [
                'status' => 422,
                'code' => 'SC_COMMON_VALIDATION_FAILED',
                'message' => 'Validation failed',
                'details' => ['errors' => $e->errors()],
                'report' => false,
            ];
        }

        // 401: auth (暫定：AuthenticationExceptionは token invalid 扱いに寄せる)
        if ($e instanceof AuthenticationException) {
            return [
                'status' => 401,
                'code' => 'SC_TOKEN_INVALID',
                'message' => 'Unauthenticated',
                'details' => ['auth' => ['scheme' => 'participant_token', 'reason' => 'missing']],
                'report' => false,
            ];
        }

        // 404: model not found (暫定：フォーム以外も来得るので共通404で受けるか、後で明示例外に寄せる)
        if ($e instanceof ModelNotFoundException) {
            return [
                'status' => 404,
                'code' => 'SC_COMMON_NOT_FOUND',
                'message' => 'Not found',
                'details' => [],
                'report' => false,
            ];
        }

        // 409: unique conflict (今入れておく価値が高い)
        if ($e instanceof QueryException) {
            $mapped = $this->mapUniqueConstraint($e);
            if ($mapped !== null) return $mapped;
        }

        if ($e instanceof UseCaseException) {
            $status = (int) $e->getCode(); // RuntimeException の code をHTTPとして使う
            if ($status < 400 || $status >= 600) {
                $status = 400; // 念のためのフォールバック
            }

            return [
                'status'  => $status,
                'code'    => $e->codeKey,
                'message' => $e->getMessage(),
                'details' => $e->detail,
                'report'  => false,
            ];
        }

        // HttpException (403/404 etc)
        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            return [
                'status' => $status,
                'code' => match ($status) {
                    403 => 'SC_COMMON_FORBIDDEN',
                    404 => 'SC_COMMON_NOT_FOUND',
                    405 => 'SC_COMMON_METHOD_NOT_ALLOWED',
                    429 => 'SC_COMMON_TOO_MANY_REQUESTS',
                    default => 'SC_COMMON_HTTP_ERROR',
                },
                'message' => match ($status) {
                    403 => 'Forbidden',
                    404 => 'Not found',
                    405 => 'Method not allowed',
                    429 => 'Too many requests',
                    default => 'HTTP error',
                },
                'details' => [],
                'report' => $status >= 500,
            ];
        }

        // 500: fallback
        return [
            'status' => 500,
            'code' => 'SC_COMMON_INTERNAL',
            'message' => 'Internal server error',
            'details' => [],
            'report' => true,
        ];
    }

    /**
     * @return array{status:int, code:string, message:string, details:array, report:bool}|null
     */
    private function mapUniqueConstraint(\Illuminate\Database\QueryException $e): ?array
    {
        $sqlState = $e->errorInfo[0] ?? null;
        $driverCode = $e->errorInfo[1] ?? null;

        $isMysqlDup = ($sqlState === '23000' && (int)$driverCode === 1062);
        $isPgUnique = ($sqlState === '23505');

        if (!($isMysqlDup || $isPgUnique)) {
            return null;
        }

        $rawMsg = (string)($e->errorInfo[2] ?? $e->getMessage());
        $constraint = $this->extractConstraintName($rawMsg) ?? 'unknown';

        // テーブル名付き/なしを吸収したいなら正規化（任意）
        $normalized = preg_replace('/^[a-zA-Z0-9_]+\./', '', $constraint);

        [$code, $details, $message] = match ($normalized) {
            'answers_unique_snapshot_question' => [
                'SC_ANSWER_UNIQUE_CONFLICT',
                ['resource' => 'answer', 'reason' => 'duplicate'],
                'Conflict',
            ],
            'snapshots_unique_form_participant' => [
                'SC_SNAPSHOT_DUPLICATE_SUBMIT',
                ['resource' => 'snapshot', 'reason' => 'already_submitted'],
                'Already submitted',
            ],
            default => [
                'SC_COMMON_CONFLICT',
                ['reason' => 'conflict'],
                'Conflict',
            ],
        };

        // constraintを details に残す（外に出したくないならここを外す）
        $details['constraint'] = $constraint;

        return [
            'status' => 409,
            'code' => $code,
            'message' => $message,   // ← 必ず定義済み
            'details' => $details,
            'report' => false,
        ];
    }

    private function extractConstraintName(string $message): ?string
    {
        if (preg_match("/for key '([^']+)'/u", $message, $m)) return $m[1];      // MySQL
        if (preg_match('/unique constraint "([^"]+)"/u', $message, $m)) return $m[1]; // PG
        return null;
    }
}
