<?php

namespace App\Infrastructure\Logging;

interface AuditLogRepository
{
    public function append(array $record): void;

    /**
     * @return array{items: array<int,array>, total: int}
     */
    public function search(array $filter, int $limit, int $offset): array;

    /**
     * @return array<int,array>
     */
    public function findByTraceId(string $traceId, int $limit = 200): array;
}
