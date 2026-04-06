<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectPeriodController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        $periods = $project->periods()
            ->orderByDesc('period')
            ->get();

        return response()->json(['data' => $periods]);
    }

    public function show(Project $project, string $period): JsonResponse
    {
        $periodModel = $project->periods()
            ->where('period', $period)
            ->firstOrFail();

        return response()->json(['data' => $periodModel]);
    }
}
