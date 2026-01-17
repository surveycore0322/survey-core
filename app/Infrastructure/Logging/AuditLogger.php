<?php

namespace App\Infrastructure\Logging;

use Carbon\CarbonImmutable;

class AuditLogger
{
    public function __construct(
        private readonly AuditLogRepository $repo,
        private readonly LogSanitizer $sanitizer,
    ) {}

    public function info(string $traceId, string $event, array $context = []): void
    {
        $this->append('info', $traceId, $event, $context);
    }

    public function warn(string $traceId, string $event, array $context = []): void
    {
        $this->append('warn', $traceId, $event, $context);
    }

    public function error(string $traceId, string $event, array $context = []): void
    {
        $this->append('error', $traceId, $event, $context);
    }

    private function append(string $level, string $traceId, string $event, array $context): void
    {
        $record = [
            'occurred_at' => CarbonImmutable::now(),
            'trace_id' => $traceId,
            'level' => $level,
            'event' => $event,
            'scope_type' => $context['scope_type'] ?? 'system',
            'scope_id' => $context['scope_id'] ?? null,
            'actor_type' => $context['actor_type'] ?? null,
            'actor_id' => $context['actor_id'] ?? null,
            'request_json' => isset($context['request'])
                ? $this->sanitizer->sanitizePayload($context['request'])
                : null,
            'io_json' => isset($context['io'])
                ? $this->sanitizer->sanitizePayload($context['io'])
                : null,
            'details_json' => isset($context['details'])
                ? $this->sanitizer->sanitizePayload($context['details'])
                : null,
        ];

        $this->repo->append($record);
    }
}
