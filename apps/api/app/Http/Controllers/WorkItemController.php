<?php

namespace App\Http\Controllers;

use App\Models\ProjectPeriod;
use App\Models\ProjectWorkItem;
use Illuminate\Http\JsonResponse;

class WorkItemController extends Controller
{
    /**
     * Level 4 — work items for a period, mapped to frontend format.
     */
    public function index(ProjectPeriod $periodModel): JsonResponse
    {
        $roots = $periodModel->workItems()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        $all = $periodModel->workItems()->orderBy('sort_order')->get()->keyBy('id');

        $items = $this->buildTree($roots, $all);

        return response()->json([
            'data' => [
                'tahap'       => $periodModel->period,
                'rabInternal' => (float) $periodModel->hpp_plan_total,
                'items'       => $items,
            ],
        ]);
    }

    private function buildTree($nodes, $all): array
    {
        return $nodes->map(function (ProjectWorkItem $node) use ($all) {
            $children = $all->filter(fn($i) => $i->parent_id === $node->id);

            return [
                'id'             => $node->id,
                'name'           => $node->item_name,
                'item_no'        => $node->item_no,
                'volume'         => null,   // not in current model, reserved for future
                'satuan'         => null,
                'harsatInternal' => null,
                'totalBiaya'     => (float) $node->total_budget,
                'realisasi'      => (float) $node->realisasi,
                'deviasi'        => (float) $node->deviasi,
                'deviasi_pct'    => (float) $node->deviasi_pct,
                'is_total_row'   => (bool) $node->is_total_row,
                'children'       => $children->isNotEmpty()
                    ? $this->buildTree($children, $all)
                    : [],
            ];
        })->values()->toArray();
    }
}
