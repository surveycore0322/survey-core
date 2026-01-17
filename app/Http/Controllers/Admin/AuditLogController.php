<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Logging\AuditLogRepository;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct(private readonly AuditLogRepository $repo) {}

    public function index(Request $request)
    {
        $limit = min((int)$request->query('limit', 50), 200);
        $offset = max((int)$request->query('offset', 0), 0);

        $filter = $request->only([
            'trace_id','level','event','scope_type','scope_id','actor_type','actor_id','from','to'
        ]);

        $res = $this->repo->search($filter, $limit, $offset);

        return response()->json([
            'total' => $res['total'],
            'items' => $res['items'],
        ]);
    }

    public function byTrace(string $traceId, Request $request)
    {
        $limit = min((int)$request->query('limit', 200), 500);

        $items = $this->repo->findByTraceId($traceId, $limit);

        return response()->json([
            'trace_id' => $traceId,
            'items' => $items,
        ]);
    }
}
