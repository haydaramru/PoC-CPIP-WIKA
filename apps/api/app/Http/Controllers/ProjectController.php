<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Http\Requests\UploadExcelRequest;
use App\Imports\ProjectImport;
use App\Models\IngestionFile;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        $query = Project::query();

        $query->byDivision($request->division);
        $query->byContractRange(
            $request->filled('min_contract') ? (float) $request->min_contract : null,
            $request->filled('max_contract') ? (float) $request->max_contract : null,
        );
        $query->byStatus($request->status);

        $year = $request->filled('year') ? (int) $request->year : null;
        $query->byYear($year);

        $sortBy  = in_array($request->sort_by, ['cpi', 'spi', 'contract_value', 'project_name'])
                    ? $request->sort_by : 'cpi';   // default CPI ascending sesuai brief
        $sortDir = $request->sort_dir === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortDir);

        $projects        = $query->get();
        $overbudgetCount = $projects->filter(fn($p) => $p->cpi < 1)->count();
        $delayCount      = $projects->filter(fn($p) => $p->spi < 1)->count();

        $availableYears = Project::distinct()
            ->orderByDesc('project_year')
            ->pluck('project_year')
            ->filter()
            ->values();

        return response()->json([
            'data' => $projects,
            'meta' => [
                'total'            => $projects->count(),
                'overbudget_count' => $overbudgetCount,
                'delay_count'      => $delayCount,
                'overbudget_pct'   => $projects->count()
                    ? round($overbudgetCount / $projects->count() * 100, 1) : 0,
                'delay_pct'        => $projects->count()
                    ? round($delayCount / $projects->count() * 100, 1) : 0,
                'available_years'  => $availableYears,
                'active_year'      => $year ?? Project::max('project_year'),
            ],
        ]);
    }


    public function summary(): JsonResponse
    {
        $all = Project::all();

        if ($all->isEmpty()) {
            return response()->json([
                'total_projects'   => 0,
                'avg_cpi'          => 0,
                'avg_spi'          => 0,
                'overbudget_pct'   => 0,
                'delay_pct'        => 0,
                'by_division'      => [],
                'status_breakdown' => [],
            ]);
        }

        $byDivision = $all->groupBy('division')->map(fn($projects) => [
            'total'            => $projects->count(),
            'avg_cpi'          => round($projects->avg('cpi'), 4),
            'avg_spi'          => round($projects->avg('spi'), 4),
            'overbudget_count' => $projects->filter(fn($p) => $p->cpi < 1)->count(),
            'delay_count'      => $projects->filter(fn($p) => $p->spi < 1)->count(),
        ]);

        return response()->json([
            'total_projects'   => $all->count(),
            'avg_cpi'          => round($all->avg('cpi'), 4),
            'avg_spi'          => round($all->avg('spi'), 4),
            'overbudget_count' => $all->filter(fn($p) => $p->cpi < 1)->count(),
            'delay_count'      => $all->filter(fn($p) => $p->spi < 1)->count(),
            'overbudget_pct'   => round($all->filter(fn($p) => $p->cpi < 1)->count() / $all->count() * 100, 1),
            'delay_pct'        => round($all->filter(fn($p) => $p->spi < 1)->count() / $all->count() * 100, 1),
            'by_division'      => $byDivision,
            'status_breakdown' => $all->groupBy('status')->map->count(),
        ]);
    }


    public function show(Project $project): JsonResponse
    {
        return response()->json(['data' => $project]);
    }


    public function store(ProjectRequest $request): JsonResponse
    {
        return response()->json(['data' => Project::create($request->validated())], 201);
    }


    public function update(ProjectRequest $request, Project $project): JsonResponse
    {
        $project->update($request->validated());
        return response()->json(['data' => $project->fresh()]);
    }


    public function destroy(Project $project): JsonResponse
    {
        $project->delete();
        return response()->json(['message' => 'Project berhasil dihapus.']);
    }

    public function upload(UploadExcelRequest $request): JsonResponse
    {
        $files = $request->file('files') ?? [];

        if (empty($files)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada file yang diterima. Pastikan key request adalah "files[]".',
            ], 422);
        }
        $results = [];

        foreach ($files as $file) {

            $storedPath = $file->store('ingestion-files', 'local');

            $ingestionFile = IngestionFile::create([
                'original_name' => $file->getClientOriginalName(),
                'stored_path'   => $storedPath,
                'disk'          => 'local',
                'status'        => 'pending',
            ]);

            try {
                $ingestionFile->markProcessing();

                $importer = new ProjectImport();
                $result   = $importer->import(
                    $ingestionFile->getAbsolutePath(),
                    $ingestionFile->id,
                );

                $ingestionFile->markDone(
                    $result['total'],
                    $result['imported'],
                    $result['skipped'],
                    $result['errors'],
                );

            } catch (\RuntimeException $e) {
                $ingestionFile->markFailed($e->getMessage());

                $result = [
                    'total'    => 0,
                    'imported' => 0,
                    'skipped'  => 0,
                    'errors'   => [$e->getMessage()],
                ];
            }

            $results[] = [
                'file_id'       => $ingestionFile->id,
                'file_name'     => $ingestionFile->original_name,
                'status'        => $ingestionFile->status,
                'total_rows'    => $result['total'],
                'imported'      => $result['imported'],
                'skipped'       => $result['skipped'],
                'errors'        => $result['errors'],
            ];
        }

        $allSuccess = collect($results)->every(fn($r) => $r['status'] === 'success');
        $anySuccess = collect($results)->contains(fn($r) => in_array($r['status'], ['success', 'partial']));
        $httpStatus = $anySuccess ? 200 : 422;

        return response()->json([
            'success' => $anySuccess,
            'message' => $allSuccess
                ? 'Semua file berhasil diimport.'
                : ($anySuccess ? 'Sebagian file berhasil diimport.' : 'Semua file gagal diimport.'),
            'results' => $results,
        ], $httpStatus);
    }

    
    public function ingestionFiles(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $files   = IngestionFile::latest()
            ->withCount('projects')
            ->paginate($perPage);

        return response()->json($files);
    }

    public function download(IngestionFile $ingestionFile): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        abort_unless($ingestionFile->fileExists(), 404, 'File tidak ditemukan di storage.');

        $absolutePath = $ingestionFile->getAbsolutePath();

        abort_unless(file_exists($absolutePath), 404, 'File fisik tidak ditemukan.');

        return response()->download($absolutePath, $ingestionFile->original_name);
    }

    public function reprocess(IngestionFile $ingestionFile): JsonResponse
    {
        abort_unless($ingestionFile->fileExists(), 404, 'File tidak ditemukan di storage.');

        // Reset status
        $ingestionFile->markProcessing();

        try {
            $importer = new ProjectImport();
            $result   = $importer->import(
                $ingestionFile->getAbsolutePath(),
                $ingestionFile->id,
            );

            $ingestionFile->markDone(
                $result['total'],
                $result['imported'],
                $result['skipped'],
                $result['errors'],
            );

        } catch (\RuntimeException $e) {
            $ingestionFile->markFailed($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success'   => true,
            'message'   => "Reprocess selesai. {$ingestionFile->imported_rows} berhasil, {$ingestionFile->skipped_rows} dilewati.",
            'file_id'   => $ingestionFile->id,
            'status'    => $ingestionFile->status,
            'imported'  => $ingestionFile->imported_rows,
            'skipped'   => $ingestionFile->skipped_rows,
            'errors'    => $ingestionFile->errors,
        ]);
    }

    public function insight(Project $project): JsonResponse
    {
        $cpi         = (float) $project->cpi;
        $spi         = (float) $project->spi;
        $plannedCost = (float) $project->planned_cost;
        $actualCost  = (float) $project->actual_cost;
        $delay       = $project->actual_duration - $project->planned_duration;
        $overrunPct  = $plannedCost > 0
            ? (($actualCost - $plannedCost) / $plannedCost) * 100
            : 0;

        $bullets = [];

        if ($cpi >= 1) {
            $bullets[] = [
                'level' => 'info',
                'text'  => "Positive cost performance: CPI {$cpi} indicates the project is under budget and cost-efficient.",
            ];
        } else {
            $bullets[] = [
                'level' => $cpi < 0.9 ? 'critical' : 'warning',
                'text'  => sprintf(
                    'Cost overrun detected: CPI %.2f indicates the project is %.1f%% over planned budget.',
                    $cpi, abs($overrunPct)
                ),
            ];
        }

        if ($spi >= 1) {
            $bullets[] = [
                'level' => 'info',
                'text'  => "Strong schedule performance: SPI {$spi} indicates the project is ahead of schedule compared to the baseline plan.",
            ];
        } elseif ($delay > 0) {
            $bullets[] = [
                'level' => $delay > 3 ? 'critical' : 'warning',
                'text'  => "Schedule delay: SPI {$spi} indicates the project is {$delay} month(s) behind the planned timeline.",
            ];
        } else {
            $bullets[] = [
                'level' => 'info',
                'text'  => "Schedule on track: SPI {$spi} indicates the project is progressing as planned.",
            ];
        }

        $sameDivision = Project::where('division', $project->division)
            ->where('id', '!=', $project->id)
            ->get();

        if ($sameDivision->isNotEmpty()) {
            $avgCpi  = round($sameDivision->avg('cpi'), 4);
            $cpiDiff = round($cpi - $avgCpi, 4);

            if (abs($cpiDiff) > 0.05) {
                $direction = $cpiDiff > 0 ? 'above' : 'below';
                $bullets[] = [
                    'level' => $cpiDiff < 0 ? 'warning' : 'info',
                    'text'  => sprintf(
                        "Division comparison: This project's CPI is %.1f%% %s the %s division average (avg CPI: %.2f).",
                        abs($cpiDiff * 100), $direction, $project->division, $avgCpi
                    ),
                ];
            }

            $alsoOverbudget = $sameDivision->filter(fn($p) => $p->cpi < 1)->count();
            if ($alsoOverbudget > 0 && $cpi < 1) {
                $bullets[] = [
                    'level' => 'warning',
                    'text'  => "{$alsoOverbudget} of {$sameDivision->count()} other {$project->division} projects are also over budget, suggesting a division-wide pattern.",
                ];
            }
        }

        if ($cpi < 0.9 && $spi < 0.9) {
            $bullets[] = [
                'level' => 'critical',
                'text'  => 'Project is facing dual issues: significant cost overrun and schedule delay. Immediate escalation and full review recommended.',
            ];
        }

        if ($cpi >= 1 && $spi >= 1) {
            $summaryLevel = 'info';
            $summaryText  = 'Overall project health is strong. The project is performing above plan with opportunities for scope optimization if the trend continues.';
        } elseif ($cpi < 0.9 && $spi < 0.9) {
            $summaryLevel = 'critical';
            $summaryText  = 'Overall project health is critical. Both cost and schedule are significantly off-track — immediate escalation and a full review are recommended.';
        } elseif ($cpi < 1 || $spi < 1) {
            $summaryLevel = 'warning';
            $summaryText  = 'Overall project health is at risk. One or more performance indicators are below target — proactive corrective actions are advised.';
        } else {
            $summaryLevel = 'info';
            $summaryText  = 'Overall project health is on track. Continue monitoring for any emerging deviations.';
        }

        return response()->json([
            'bullets' => $bullets,
            'summary' => [
                'level' => $summaryLevel,
                'text'  => $summaryText,
            ],
        ]);
    }
}