<?php

namespace App\Http\Controllers;

use App\Models\HarsatHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HarsatController extends Controller
{
    /**
     * GET /harsat/trend
     * Returns trend data in the format expected by TrendHarsatUtama component:
     * { years, categories: [{key, label}], data: { key: [values...] } }
     */
    public function trend(Request $request): JsonResponse
    {
        // Primary: query from material logs (auto-populated from Excel import)
        $rows = DB::table('project_material_logs')
            ->select('material_type', 'tahun_perolehan')
            ->selectRaw('AVG(harga_satuan) as avg_harsat')
            ->whereNotNull('material_type')
            ->whereNotNull('tahun_perolehan')
            ->whereNotNull('harga_satuan')
            ->where('harga_satuan', '>', 0)
            ->where('is_discount', false)
            ->groupBy('material_type', 'tahun_perolehan')
            ->orderBy('tahun_perolehan')
            ->orderBy('material_type')
            ->get();

        // Fallback: harsat_histories table (manual input)
        if ($rows->isEmpty()) {
            $manual = HarsatHistory::orderBy('year')->orderBy('category_key')->get();

            if ($manual->isEmpty()) {
                return response()->json(['data' => null]);
            }

            $years      = $manual->pluck('year')->unique()->sort()->values()->map(fn($y) => (string) $y);
            $categories = $manual->unique('category_key')
                ->map(fn($r) => ['key' => $r->category_key, 'label' => $r->category])
                ->values();

            $data = [];
            foreach ($categories as $cat) {
                $data[$cat['key']] = $years->map(function ($year) use ($manual, $cat) {
                    $row = $manual->first(fn($r) => $r->category_key === $cat['key'] && (string) $r->year === $year);
                    return $row ? round((float) $row->value, 2) : 0;
                })->values()->toArray();
            }

            return response()->json(['data' => ['years' => $years->toArray(), 'categories' => $categories->toArray(), 'data' => $data]]);
        }

        // Build structure from material logs
        $years = $rows->pluck('tahun_perolehan')->unique()->sort()->values();

        $categories = $rows->pluck('material_type')->unique()->sort()->values()
            ->map(fn($label) => [
                'key'   => \Str::slug($label, '_'),
                'label' => $label,
            ]);

        $data = [];
        foreach ($categories as $cat) {
            $data[$cat['key']] = $years->map(function ($year) use ($rows, $cat) {
                $row = $rows->first(
                    fn($r) => \Str::slug($r->material_type, '_') === $cat['key']
                           && $r->tahun_perolehan === $year
                );
                // Convert from raw IDR to Jillion (÷ 1,000,000,000)
                return $row ? round((float) $row->avg_harsat / 1_000_000_000, 2) : 0;
            })->values()->toArray();
        }

        return response()->json([
            'data' => [
                'years'      => $years->toArray(),
                'categories' => $categories->toArray(),
                'data'       => $data,
            ],
        ]);
    }

    /**
     * POST /harsat (protected) — upsert a single data point.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category'     => 'required|string|max:100',
            'category_key' => 'required|string|max:50',
            'year'         => 'required|integer|min:2000|max:2099',
            'value'        => 'required|numeric|min:0',
            'unit'         => 'nullable|string|max:50',
        ]);

        $row = HarsatHistory::updateOrCreate(
            ['category_key' => $validated['category_key'], 'year' => $validated['year']],
            $validated,
        );

        return response()->json(['data' => $row], 201);
    }
}
