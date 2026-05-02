<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectDirectCost;
use App\Models\ProjectIndirectCost;
use App\Models\ProjectIndirectCostItem;
use App\Models\ProjectOtherCost;
use App\Models\ProjectOtherCostItem;
use App\Models\ProjectProfitLoss;
use App\Models\ProjectSale;
use App\Models\ProjectWorkItem;

class FinancialSummaryAggregator
{
    private const INDIRECT_BUCKETS = [
        'fasilitas', 'sekretariat', 'kendaraan', 'personalia', 'keuangan', 'umum',
    ];

    public function rebuild(Project $project): void
    {
        $key = ['project_id' => $project->id];

        $penjualan = (float) ($project->contract_value ?? $project->bq_external ?? 0);
        ProjectSale::updateOrCreate($key, ['penjualan' => $penjualan]);

        $direct = $this->aggregateDirectCost($project->id);
        ProjectDirectCost::updateOrCreate($key, $direct);

        $indirect = $this->aggregateIndirectCost($project->id);
        ProjectIndirectCost::updateOrCreate($key, $indirect);

        $other = $this->aggregateOtherCost($project->id);
        ProjectOtherCost::updateOrCreate($key, $other);

        $totalDirect   = array_sum($direct);
        $totalIndirect = array_sum($indirect);
        $totalOther    = array_sum($other);
        $bebanPph      = $penjualan * (float) ($project->tarif_pph_final ?? 0);
        $labaKotor     = $penjualan - ($totalDirect + $totalIndirect + $totalOther);

        ProjectProfitLoss::updateOrCreate($key, [
            'beban_pph_final' => $bebanPph,
            'laba_kotor'      => $labaKotor,
        ]);
    }

    private function aggregateDirectCost(int $projectId): array
    {
        $rows = ProjectWorkItem::query()
            ->whereHas('period', fn($q) => $q->where('project_id', $projectId))
            ->where('bobot_pct', '>', 0)
            ->get(['cost_subcategory', 'volume_actual', 'harsat_actual']);

        $totals = ['material' => 0.0, 'upah' => 0.0, 'alat' => 0.0, 'subkon' => 0.0];

        foreach ($rows as $row) {
            $sub = strtolower(trim((string) ($row->cost_subcategory ?? '')));
            if ($sub === '') continue;
            $bucket = $this->matchDirectBucket($sub);
            if ($bucket === null) continue;
            $totals[$bucket] += (float) ($row->volume_actual ?? 0) * (float) ($row->harsat_actual ?? 0);
        }

        return $totals;
    }

    private function matchDirectBucket(string $sub): ?string
    {
        return match (true) {
            str_contains($sub, 'material')                                          => 'material',
            str_contains($sub, 'upah') || str_contains($sub, 'tenaga') || str_contains($sub, 'labor') => 'upah',
            str_contains($sub, 'alat') || str_contains($sub, 'equipment')           => 'alat',
            str_contains($sub, 'subkon') || str_contains($sub, 'subcontract') || str_contains($sub, 'sub-contract') => 'subkon',
            default => null,
        };
    }

    private function aggregateIndirectCost(int $projectId): array
    {
        $rows = ProjectIndirectCostItem::where('project_id', $projectId)
            ->get(['sub_kategori', 'realisasi']);

        $totals = array_fill_keys(self::INDIRECT_BUCKETS, 0.0);

        foreach ($rows as $row) {
            $bucket = $this->normalizeIndirectBucket((string) ($row->sub_kategori ?? ''));
            $totals[$bucket] += (float) ($row->realisasi ?? 0);
        }

        return $totals;
    }

    private function normalizeIndirectBucket(string $raw): string
    {
        $key = strtolower(trim($raw));
        foreach (self::INDIRECT_BUCKETS as $bucket) {
            if (str_contains($key, $bucket)) return $bucket;
        }
        return 'umum';
    }

    private function aggregateOtherCost(int $projectId): array
    {
        $rows = ProjectOtherCostItem::where('project_id', $projectId)
            ->get(['kategori', 'nilai']);

        $totals = ['biaya_pemeliharaan' => 0.0, 'risiko' => 0.0];

        foreach ($rows as $row) {
            $kat = strtolower(trim((string) ($row->kategori ?? '')));
            $nilai = (float) ($row->nilai ?? 0);
            if (str_contains($kat, 'pemeliharaan') || str_contains($kat, 'maintenance')) {
                $totals['biaya_pemeliharaan'] += $nilai;
            } elseif (str_contains($kat, 'risiko') || str_contains($kat, 'risk')) {
                $totals['risiko'] += $nilai;
            }
        }

        return $totals;
    }
}
