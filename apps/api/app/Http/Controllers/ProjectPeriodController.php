<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectPeriod;
use Illuminate\Http\JsonResponse;

class ProjectPeriodController extends Controller
{
    /**
     * Level 3 — list of phases (periods) for a project.
     * Maps backend fields to the BQ vs RAB format expected by the frontend.
     */
    public function index(Project $project): JsonResponse
    {
        $periods = $project->periods()
            ->orderBy('id')
            ->get();

        $phases = $periods->map(fn(ProjectPeriod $p) => [
            'id'          => $p->id,
            'name'        => $p->period,
            'bqExternal'  => (float) $p->total_pagu,        // nilai dari owner/client
            'rabInternal' => (float) $p->hpp_plan_total,    // HPP internal (RAB)
            'realisasi'   => (float) $p->hpp_actual_total,  // realisasi biaya
            'deviasi'     => (float) $p->hpp_deviation,     // deviasi %
        ]);

        return response()->json([
            'data' => [
                'project_name'  => $project->project_name,
                'sbu'           => $project->sbu,
                'owner'         => $project->owner,
                'contract_type' => $project->contract_type,
                'phases'        => $phases,
            ],
        ]);
    }

    /**
     * Level 3 detail — single period with work items.
     */
    public function show(Project $project, ProjectPeriod $periodModel): JsonResponse
    {
        abort_unless($periodModel->project_id === $project->id, 404);

        $workItems = $periodModel->workItems()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get()
            ->map(fn($item) => [
                'id'             => $item->id,
                'name'           => $item->item_name,
                'volume'         => null,
                'satuan'         => null,
                'harsatInternal' => null,
                'totalBiaya'     => (float) $item->total_budget,
                'realisasi'      => (float) $item->realisasi,
                'deviasi'        => (float) $item->deviasi,
                'deviasi_pct'    => (float) $item->deviasi_pct,
            ]);

        return response()->json([
            'data' => [
                'tahap'       => $periodModel->period,
                'rabInternal' => (float) $periodModel->hpp_plan_total,
                'items'       => $workItems,
            ],
        ]);
    }
}
