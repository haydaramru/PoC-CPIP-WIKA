<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;

class ProgressCurveController extends Controller
{
    /**
     * Level 7 — S-curve + timeline for a project.
     * Transforms weekly data into the monthly aggregated format expected by the frontend.
     */
    public function index(Project $project): JsonResponse
    {
        $curves = $project->progressCurves()
            ->orderBy('week_number')
            ->get();

        if ($curves->isEmpty()) {
            return response()->json([
                'data' => [
                    'timeline' => $project->timeline,
                    'spi_value'  => (float) $project->spi,
                    'spi_status' => $this->spiStatus((float) $project->spi),
                    'sCurve'     => null,
                ],
            ]);
        }

        // Aggregate weekly data into monthly buckets
        $byMonth = $curves->groupBy(fn($c) => $c->week_date->format('M'));

        $months  = $byMonth->keys()->toArray();
        $plan    = $byMonth->map(fn($g) => round((float) $g->last()->rencana_pct, 1))->values()->toArray();
        $actual  = $byMonth->map(fn($g) => round((float) $g->last()->realisasi_pct, 1))->values()->toArray();

        return response()->json([
            'data' => [
                'timeline'   => $project->timeline,
                'spi_value'  => (float) $project->spi,
                'spi_status' => $this->spiStatus((float) $project->spi),
                'sCurve'     => [
                    'months' => $months,
                    'plan'   => $plan,
                    'actual' => $actual,
                ],
                'raw' => $curves->map(fn($c) => [
                    'week_number'   => $c->week_number,
                    'week_date'     => $c->week_date->toDateString(),
                    'rencana_pct'   => (float) $c->rencana_pct,
                    'realisasi_pct' => (float) $c->realisasi_pct,
                    'deviasi_pct'   => (float) $c->deviasi_pct,
                    'keterangan'    => $c->keterangan,
                ]),
            ],
        ]);
    }

    private function spiStatus(float $spi): string
    {
        return match(true) {
            $spi >= 1.0  => 'On Schedule',
            $spi >= 0.9  => 'Slight Delay',
            $spi >= 0.8  => 'Moderate Delay',
            default      => 'Critical Delay',
        };
    }
}
