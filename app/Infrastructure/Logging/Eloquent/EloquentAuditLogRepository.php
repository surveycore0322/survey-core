<?php

namespace App\Infrastructure\Logging\Eloquent;

use App\Infrastructure\Logging\AuditLogRepository;

class EloquentAuditLogRepository implements AuditLogRepository
{
    public function append(array $record): void
    {
        AuditLogEloquent::query()->create($record);
    }

    public function search(array $filter, int $limit, int $offset): array
    {
        $q = AuditLogEloquent::query()->orderByDesc('occurred_at');

        foreach (['trace_id','level','event','scope_type','scope_id','actor_type','actor_id'] as $k) {
            if (!empty($filter[$k])) $q->where($k, $filter[$k]);
        }

        if (!empty($filter['from'])) $q->where('occurred_at', '>=', $filter['from']);
        if (!empty($filter['to']))   $q->where('occurred_at', '<=', $filter['to']);

        $total = (clone $q)->count();

        $items = $q->limit($limit)->offset($offset)->get()->map(fn($m) => $m->toArray())->all();

        return ['items' => $items, 'total' => $total];
    }

    public function findByTraceId(string $traceId, int $limit = 200): array
    {
        return AuditLogEloquent::query()
            ->where('trace_id', $traceId)
            ->orderBy('occurred_at')
            ->limit($limit)
            ->get()
            ->map(fn($m) => $m->toArray())
            ->all();
    }
}
