<?php

namespace App\Infrastructure\Logging;

use Throwable;

class LogSanitizer
{
    private const SENSITIVE_HEADERS = [
        'authorization', 'cookie', 'set-cookie',
    ];

    private const SENSITIVE_KEYS = [
        'token', 'password', 'secret', 'authorization',
    ];

    public function sanitizeHeaders(array $headers): array
    {
        $out = [];
        foreach ($headers as $k => $v) {
            $lk = strtolower((string)$k);
            if (in_array($lk, self::SENSITIVE_HEADERS, true)) {
                $out[$k] = '[REDACTED]';
                continue;
            }
            // Symfony header bag may be array
            $out[$k] = is_array($v) ? $this->clip(implode(',', $v), 300) : $this->clip((string)$v, 300);
        }
        return $out;
    }

    public function sanitizePayload(array $payload): array
    {
        return $this->deepSanitize($payload, 0);
    }

    public function summarizeValidationErrors(array $errors): array
    {
        // errors: field => [messages...]
        $out = [];
        foreach ($errors as $field => $messages) {
            $out[] = ['field' => (string)$field, 'count' => is_array($messages) ? count($messages) : 1];
        }
        return $out;
    }

    public function sanitizeException(Throwable $e): array
    {
        return [
            'exception_class' => get_class($e),
            'message' => $this->clip($e->getMessage(), 500),
            // stackはDBに持たない（重い・漏洩リスク）。必要なら運用ログへ。
        ];
    }

    private function deepSanitize(mixed $value, int $depth): mixed
    {
        if ($depth >= 6) return '[TRUNCATED_DEPTH]';

        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $lk = strtolower((string)$k);
                if (in_array($lk, self::SENSITIVE_KEYS, true)) {
                    $out[$k] = '[REDACTED]';
                    continue;
                }
                $out[$k] = $this->deepSanitize($v, $depth + 1);
            }
            return $out;
        }

        if (is_string($value)) {
            return $this->clip($value, 800);
        }

        return $value;
    }

    private function clip(string $s, int $max): string
    {
        if (mb_strlen($s) <= $max) return $s;
        return mb_substr($s, 0, $max) . '…';
    }
}
