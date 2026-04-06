<?php

namespace App\Http\Controllers;

use App\Models\ProjectPeriod;
use App\Models\ProjectWorkItem;
use Illuminate\Http\JsonResponse;

class WorkItemController extends Controller
{
    public function index(ProjectPeriod $period): JsonResponse
    {
        $roots = $period->workItems()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        $all = $period->workItems()->orderBy('sort_order')->get()->keyBy('id');

        return response()->json(['data' => $this->buildTree($roots, $all)]);
    }

    private function buildTree($nodes, $all): array
    {
        return $nodes->map(function (ProjectWorkItem $node) use ($all) {
            $row = $node->toArray();
            $children = $all->filter(fn($i) => $i->parent_id === $node->id);
            $row['children'] = $children->isNotEmpty()
                ? $this->buildTree($children, $all)
                : [];
            return $row;
        })->values()->toArray();
    }
}
