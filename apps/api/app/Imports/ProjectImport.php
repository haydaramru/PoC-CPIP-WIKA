<?php

namespace App\Imports;

use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProjectImport
{
    /**
     * Kolom wajib yang harus ada di header Excel (case-insensitive)
     */
    private const REQUIRED_COLUMNS = [
        'project_code',
        'project_name',
        'contract_value',
        'planned_cost',
        'actual_cost',
        'planned_duration',
        'actual_duration',
    ];

    private array $errors   = [];
    private int   $imported = 0;
    private int   $skipped  = 0;

    /**
     * Parse file Excel dan import ke database.
     * Return summary hasil import.
     */
    public function import(string $filePath): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, false); // indexed from 0

        if (empty($rows)) {
            throw new \RuntimeException('File Excel kosong.');
        }

        // Baris pertama = header
        $headers = $this->normalizeHeaders($rows[0]);
        $headers = $this->applyAliases($headers);
        $this->validateHeaders($headers);

        // Proses baris data (mulai dari index 1)
        $dataRows = array_slice($rows, 1);

        foreach ($dataRows as $rowIndex => $row) {
            $lineNumber = $rowIndex + 2; // +2 karena header di baris 1

            // Skip baris kosong
            if ($this->isEmptyRow($row)) continue;

            // Map kolom ke key
            $data = array_combine($headers, $row);

            // Validasi per baris
            $validator = $this->makeValidator($data, $lineNumber);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $this->errors[] = "Baris {$lineNumber}: {$error}";
                }
                $this->skipped++;
                continue;
            }

            // Upsert: jika project_code sudah ada → update, belum ada → insert
            // KPI dihitung otomatis oleh Model::saving()
            Project::updateOrCreate(
                ['project_code' => trim($data['project_code'])],
                [
                    'project_name'     => trim($data['project_name']),
                    'division'         => isset($data['division']) ? trim($data['division']) : 'Infrastructure',
                    'owner'            => isset($data['owner']) ? trim($data['owner']) : null,
                    'contract_value'   => (float) $data['contract_value'],
                    'planned_cost'     => (float) $data['planned_cost'],
                    'actual_cost'      => (float) $data['actual_cost'],
                    'planned_duration' => (int)   $data['planned_duration'],
                    'actual_duration'  => (int)   $data['actual_duration'],
                    'progress_pct'     => isset($data['progress_pct']) ? (float) $data['progress_pct'] : 100,
                ]
            );

            $this->imported++;
        }

        return [
            'imported' => $this->imported,
            'skipped'  => $this->skipped,
            'errors'   => $this->errors,
        ];
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * Normalize header dari Excel:
     * - lowercase
     * - trim whitespace
     * - spasi/dash → underscore
     * - hapus karakter non-alphanumeric kecuali underscore
     *
     * Contoh:
     *   "Project Kode"   → "project_kode"  ← lalu di-alias ke "project_code"
     *   "Contract Value" → "contract_value"
     *   "Planned-Cost"   → "planned_cost"
     */
    private function normalizeHeaders(array $rawHeaders): array
    {
        return array_map(function ($h) {
            $h = strtolower(trim((string) $h));
            $h = preg_replace('/[\s\-]+/', '_', $h);   // spasi & dash → underscore
            $h = preg_replace('/[^\w]/', '', $h);       // hapus karakter aneh
            return $h;
        }, $rawHeaders);
    }

    /**
     * Alias header — mapping nama kolom di Excel ke nama kolom standar sistem.
     * Tambahkan di sini kalau ada variasi penulisan baru dari user.
     */
    private function applyAliases(array $headers): array
    {
        $aliases = [
            // project_code
            'project_kode'          => 'project_code',
            'kode_project'          => 'project_code',
            'kode'                  => 'project_code',
            'code'                  => 'project_code',

            // project_name
            'nama_project'          => 'project_name',
            'nama'                  => 'project_name',
            'project'               => 'project_name',

            // division — opsional, tidak wajib ada di Excel ini
            'divisi'                => 'division',
            'div'                   => 'division',

            // contract_value — dari Excel: "contract_value_m"
            'contract_value_m'      => 'contract_value',
            'nilai_kontrak'         => 'contract_value',
            'contract'              => 'contract_value',
            'kontrak'               => 'contract_value',
            'nilai'                 => 'contract_value',

            // planned_cost — dari Excel: "planned_cost_m"
            'planned_cost_m'        => 'planned_cost',
            'rencana_biaya'         => 'planned_cost',
            'biaya_rencana'         => 'planned_cost',
            'planned'               => 'planned_cost',

            // actual_cost — dari Excel: "actual_cost_m"
            'actual_cost_m'         => 'actual_cost',
            'biaya_aktual'          => 'actual_cost',
            'aktual_biaya'          => 'actual_cost',
            'actual'                => 'actual_cost',
            'realisasi_biaya'       => 'actual_cost',

            // planned_duration — dari Excel: "planned_duration_month"
            'planned_duration_month'=> 'planned_duration',
            'durasi_rencana'        => 'planned_duration',
            'rencana_durasi'        => 'planned_duration',
            'planned_dur'           => 'planned_duration',

            // actual_duration
            'durasi_aktual'         => 'actual_duration',
            'aktual_durasi'         => 'actual_duration',
            'actual_dur'            => 'actual_duration',
            'realisasi_durasi'      => 'actual_duration',

            // owner
            'pemilik'               => 'owner',

            // progress_pct — dari Excel: "progress_"
            'progress_'             => 'progress_pct',
            'progress'              => 'progress_pct',
            'progres'               => 'progress_pct',
        ];

        return array_map(fn($h) => $aliases[$h] ?? $h, $headers);
    }

    /**
     * Pastikan semua kolom wajib ada di header setelah normalisasi & alias
     */
    private function validateHeaders(array $headers): void
    {
        $missing = array_diff(self::REQUIRED_COLUMNS, $headers);

        if (!empty($missing)) {
            throw new \RuntimeException(
                'Kolom wajib tidak ditemukan: ' . implode(', ', $missing) . '. ' .
                'Header yang terbaca: ' . implode(', ', array_filter($headers)) . '. ' .
                'Pastikan nama kolom sesuai atau lihat format yang didukung.'
            );
        }
    }

    /**
     * Cek apakah baris benar-benar kosong (semua cell null/empty)
     */
    private function isEmptyRow(array $row): bool
    {
        return empty(array_filter($row, fn($cell) => $cell !== null && $cell !== ''));
    }

    /**
     * Validasi data per baris sebelum insert
     */
    private function makeValidator(array $data, int $lineNumber): \Illuminate\Validation\Validator
    {
        return Validator::make($data, [
            'project_code'     => 'required|string|max:20',
            'project_name'     => 'required|string|max:255',
            'division'         => 'nullable|in:Infrastructure,Building',
            'contract_value'   => 'required|numeric|min:0',
            'planned_cost'     => 'required|numeric|min:0',
            'actual_cost'      => 'required|numeric|min:0',
            'planned_duration' => 'required|integer|min:1',
            'actual_duration'  => 'required|integer|min:1',
            'progress_pct'     => 'nullable|numeric|min:0|max:100',
        ], [
            'division.in' => 'Division harus Infrastructure atau Building.',
        ]);
    }
}