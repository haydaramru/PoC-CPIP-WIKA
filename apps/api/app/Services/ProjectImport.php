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
        'division',
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
                    'division'         => trim($data['division']),
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
     * Lowercase & trim semua header dari Excel
     */
    private function normalizeHeaders(array $rawHeaders): array
    {
        return array_map(fn($h) => strtolower(trim((string) $h)), $rawHeaders);
    }

    /**
     * Pastikan semua kolom wajib ada di header
     */
    private function validateHeaders(array $headers): void
    {
        $missing = array_diff(self::REQUIRED_COLUMNS, $headers);

        if (!empty($missing)) {
            throw new \RuntimeException(
                'Kolom wajib tidak ditemukan di Excel: ' . implode(', ', $missing) . '. ' .
                'Pastikan baris pertama adalah header dengan nama kolom yang benar.'
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
            'division'         => 'required|in:Infrastructure,Building',
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