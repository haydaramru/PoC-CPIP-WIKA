<?php

namespace App\Http\Controllers;

use App\Models\ProjectPeriod;
use Illuminate\Http\JsonResponse;

class MaterialLogController extends Controller
{
    public function index(ProjectPeriod $period): JsonResponse
    {
        $logs = $period->materialLogs()
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $logs,
            'meta' => [
                'total_tagihan' => $logs->sum(fn($l) => (float) $l->total_tagihan),
                'total_rows'    => $logs->count(),
                'discount_rows' => $logs->where('is_discount', true)->count(),
            ],
        ]);
    }
}
