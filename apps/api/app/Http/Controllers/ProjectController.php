<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Http\Requests\UploadExcelRequest;
use App\Imports\ProjectImport;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // =========================================================================
    // GET /api/projects
    // Query params: division, sort_by, sort_dir, min_contract, max_contract, status
    // =========================================================================
    public function index(Request $request): JsonResponse
    {
        $query = Project::query();

        // Filtering
        $query->byDivision($request->division);
        $query->byContractRange(
            $request->filled('min_contract') ? (float) $request->min_contract : null,
            $request->filled('max_contract') ? (float) $request->max_contract : null,
        );
        $query->byStatus($request->status);

        // Sorting
        $sortBy  = in_array($request->sort_by, ['cpi', 'spi', 'contract_value', 'project_name'])
                    ? $request->sort_by
                    : 'project_code';
        $sortDir = $request->sort_dir === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortDir);

        $projects = $query->get();

        // Meta agregat
        $overbudgetCount = $projects->filter(fn($p) => $p->cpi < 1)->count();
        $delayCount      = $projects->filter(fn($p) => $p->spi < 1)->count();

        return response()->json([
            'data' => $projects,
            'meta' => [
                'total'            => $projects->count(),
                'overbudget_count' => $overbudgetCount,
                'delay_count'      => $delayCount,
                'overbudget_pct'   => $projects->count()
                    ? round($overbudgetCount / $projects->count() * 100, 1)
                    : 0,
                'delay_pct'        => $projects->count()
                    ? round($delayCount / $projects->count() * 100, 1)
                    : 0,
            ],
        ]);
    }

    // =========================================================================
    // GET /api/projects/summary  — untuk Dashboard halaman 1
    // =========================================================================
    public function summary(): JsonResponse
    {
        $all = Project::all();

        if ($all->isEmpty()) {
            return response()->json([
                'total_projects'  => 0,
                'avg_cpi'         => 0,
                'avg_spi'         => 0,
                'overbudget_pct'  => 0,
                'delay_pct'       => 0,
                'by_division'     => [],
                'status_breakdown'=> [],
            ]);
        }

        // Per-division breakdown
        $byDivision = $all->groupBy('division')->map(function ($projects, $division) {
            return [
                'total'            => $projects->count(),
                'avg_cpi'          => round($projects->avg('cpi'), 4),
                'avg_spi'          => round($projects->avg('spi'), 4),
                'overbudget_count' => $projects->filter(fn($p) => $p->cpi < 1)->count(),
                'delay_count'      => $projects->filter(fn($p) => $p->spi < 1)->count(),
            ];
        });

        // Status breakdown
        $statusBreakdown = $all->groupBy('status')->map->count();

        return response()->json([
            'total_projects'   => $all->count(),
            'avg_cpi'          => round($all->avg('cpi'), 4),
            'avg_spi'          => round($all->avg('spi'), 4),
            'overbudget_count' => $all->filter(fn($p) => $p->cpi < 1)->count(),
            'delay_count'      => $all->filter(fn($p) => $p->spi < 1)->count(),
            'overbudget_pct'   => round($all->filter(fn($p) => $p->cpi < 1)->count() / $all->count() * 100, 1),
            'delay_pct'        => round($all->filter(fn($p) => $p->spi < 1)->count() / $all->count() * 100, 1),
            'by_division'      => $byDivision,
            'status_breakdown' => $statusBreakdown,
        ]);
    }

    // =========================================================================
    // GET /api/projects/{project}
    // =========================================================================
    public function show(Project $project): JsonResponse
    {
        return response()->json(['data' => $project]);
    }

    // =========================================================================
    // POST /api/projects
    // =========================================================================
    public function store(ProjectRequest $request): JsonResponse
    {
        $project = Project::create($request->validated());

        return response()->json(['data' => $project], 201);
    }

    // =========================================================================
    // PUT /api/projects/{project}
    // =========================================================================
    public function update(ProjectRequest $request, Project $project): JsonResponse
    {
        $project->update($request->validated());

        return response()->json(['data' => $project->fresh()]);
    }

    // =========================================================================
    // DELETE /api/projects/{project}
    // =========================================================================
    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->json(['message' => 'Project berhasil dihapus.']);
    }

    // =========================================================================
    // POST /api/projects/upload  — Upload Excel bulk
    // =========================================================================
    public function upload(UploadExcelRequest $request): JsonResponse
    {
        // Simpan file sementara
        // $path = $request->file('file')->store('temp');
        // $fullPath = storage_path('app/' . $path);
        $fullPath = $request->file('file')->getRealPath();

        try {
            $importer = new ProjectImport();
            $result   = $importer->import($fullPath);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'success' => false,
            ], 422);
        } finally {
            // Hapus file temp setelah selesai
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        $success = $result['imported'] > 0 || $result['skipped'] === 0;

        return response()->json([
            'success'  => $success,
            'message'  => "Import selesai. {$result['imported']} project berhasil, {$result['skipped']} dilewati.",
            'imported' => $result['imported'],
            'skipped'  => $result['skipped'],
            'errors'   => $result['errors'],
        ], $success ? 200 : 422);
    }
}