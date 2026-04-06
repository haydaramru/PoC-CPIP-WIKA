<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;

class ProgressCurveController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        $curves = $project->progressCurves()
            ->orderBy('week_number')
            ->get();

        return response()->json(['data' => $curves]);
    }
}
