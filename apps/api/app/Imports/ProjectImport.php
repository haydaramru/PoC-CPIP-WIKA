<?php

namespace App\Imports;

use App\Models\Project;
use Illuminate\Support\Facades\Validator;

class ProjectImport
{
    private const REQUIRED_COLUMNS = [
        'project_code',
        'project_name',
        'division',
        'contract_value',
        'planned_cost',
        'actual_cost',
        'project_year',
        'planned_duration',
        'actual_duration',
    ];

    /**
     * Semua kemungkinan alias header dari Excel.
     * Key   = hasil normalizeHeader() dari Excel
     * Value = nama kolom standar sistem
     */
    private const ALIASES = [
        // ── project_code ──────────────────────────────────────────────────────
        'project_kode'          => 'project_code',
        'kode_project'          => 'project_code',
        'kode_proyek'           => 'project_code',
        'kode'                  => 'project_code',
        'code'                  => 'project_code',
        'id_proyek'             => 'project_code',
        'no_registrasi'         => 'project_code',
        'kode_unit'             => 'project_code',
        'seri_proyek'           => 'project_code',

        // ── project_name ──────────────────────────────────────────────────────
        'nama_project'          => 'project_name',
        'nama_proyek'           => 'project_name',
        'nama'                  => 'project_name',
        'project'               => 'project_name',
        'judul_proyek'          => 'project_name',
        'nama_gedung'           => 'project_name',
        'nama_pekerjaan'        => 'project_name',
        'deskripsi_proyek'      => 'project_name',

        // ── project_year ──────────────────────────────────────────────────────
        'year'                  => 'project_year',
        'tahun'                 => 'project_year',
        'periode'               => 'project_year',
        'tahun_anggaran'        => 'project_year',
        'tahun_pelaksanaan'     => 'project_year',
        'tahun_proyek'          => 'project_year',

        // ── division ──────────────────────────────────────────────────────────
        'divisi'                => 'division',
        'div'                   => 'division',
        'departemen'            => 'division',
        'kategori_proyek'       => 'division',
        'sektor'                => 'division',
        'bidang'                => 'division',

        // ── contract_value ────────────────────────────────────────────────────
        'contract_value_m'      => 'contract_value',
        'nilai_kontrak'         => 'contract_value',
        'nilai_kontrak_m'       => 'contract_value',
        'total_kontrak'         => 'contract_value',
        'total_kontrak_m'       => 'contract_value',
        'valuasi_kontrak'       => 'contract_value',
        'valuasi_kontrak_m'     => 'contract_value',
        'harga_kontrak'         => 'contract_value',
        'harga_kontrak_m'       => 'contract_value',
        'nilai_investasi'       => 'contract_value',
        'nilai_investasi_m'     => 'contract_value',
        'contract'              => 'contract_value',
        'kontrak'               => 'contract_value',
        'nilai'                 => 'contract_value',

        // ── planned_cost ──────────────────────────────────────────────────────
        'planned_cost_m'        => 'planned_cost',
        'rencana_biaya'         => 'planned_cost',
        'rencana_biaya_m'       => 'planned_cost',
        'anggaran_terencana'    => 'planned_cost',
        'anggaran_terencana_m'  => 'planned_cost',
        'budget_rencana'        => 'planned_cost',
        'budget_rencana_m'      => 'planned_cost',
        'rencana_pengeluaran'   => 'planned_cost',
        'rencana_pengeluaran_m' => 'planned_cost',
        'plafon_biaya'          => 'planned_cost',
        'plafon_biaya_m'        => 'planned_cost',
        'biaya_rencana'         => 'planned_cost',
        'planned'               => 'planned_cost',

        // ── actual_cost ───────────────────────────────────────────────────────
        'actual_cost_m'         => 'actual_cost',
        'biaya_aktual'          => 'actual_cost',
        'biaya_aktual_m'        => 'actual_cost',
        'pengeluaran_riil'      => 'actual_cost',
        'pengeluaran_riil_m'    => 'actual_cost',
        'total_biaya_akhir'     => 'actual_cost',
        'total_biaya_akhir_m'   => 'actual_cost',
        'realisasi_biaya'       => 'actual_cost',
        'realisasi_biaya_m'     => 'actual_cost',
        'serapan_biaya'         => 'actual_cost',
        'serapan_biaya_m'       => 'actual_cost',
        'biaya_aktual_biaya'    => 'actual_cost',
        'aktual_biaya'          => 'actual_cost',
        'actual'                => 'actual_cost',

        // ── planned_duration ──────────────────────────────────────────────────
        'planned_duration_month'  => 'planned_duration',
        'planned_duration_bulan'  => 'planned_duration',
        'rencana_durasi'          => 'planned_duration',
        'rencana_durasi_bulan'    => 'planned_duration',
        'target_waktu'            => 'planned_duration',
        'target_waktu_bulan'      => 'planned_duration',
        'estimasi_durasi'         => 'planned_duration',
        'estimasi_durasi_bulan'   => 'planned_duration',
        'jadwal_kerja'            => 'planned_duration',
        'jadwal_kerja_bulan'      => 'planned_duration',
        'durasi_pengerjaan'       => 'planned_duration',
        'durasi_pengerjaan_bulan' => 'planned_duration',
        'durasi_rencana'          => 'planned_duration',
        'planned_dur'             => 'planned_duration',

        // ── actual_duration ───────────────────────────────────────────────────
        'durasi_aktual'           => 'actual_duration',
        'durasi_aktual_bulan'     => 'actual_duration',
        'waktu_realisasi'         => 'actual_duration',
        'durasi_final'            => 'actual_duration',
        'masa_pelaksanaan'        => 'actual_duration',
        'durasi_terpakai'         => 'actual_duration',
        'aktual_durasi'           => 'actual_duration',
        'actual_dur'              => 'actual_duration',
        'realisasi_durasi'        => 'actual_duration',

        // ── owner ─────────────────────────────────────────────────────────────
        'pemilik'                 => 'owner',
        'instansi'                => 'owner',
        'klien'                   => 'owner',
        'penyelenggara'           => 'owner',

        // ── progress_pct ──────────────────────────────────────────────────────
        'progress_'               => 'progress_pct',
        'progress'                => 'progress_pct',
        'progres'                 => 'progress_pct',
        'progress_persen'         => 'progress_pct',
    ];

    private array $errors   = [];
    private int   $imported = 0;
    private int   $skipped  = 0;
    private int   $total    = 0;

    public function import(string $filePath, ?int $ingestionFileId = null): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $raw         = $sheet->toArray(null, true, true, false);

        if (empty($raw)) {
            throw new \RuntimeException('File Excel kosong.');
        }

        // ── Auto-detect & normalize layout ────────────────────────────────────
        $rows = $this->isTransposed($raw) ? $this->transpose($raw) : $raw;

        // ── Header processing ─────────────────────────────────────────────────
        $headers = $this->normalizeHeaders($rows[0]);
        $headers = $this->applyAliases($headers);
        $this->validateHeaders($headers);

        // ── Data rows ─────────────────────────────────────────────────────────
        $dataRows = array_slice($rows, 1);

        foreach ($dataRows as $rowIndex => $row) {
            $lineNumber = $rowIndex + 2;

            if ($this->isEmptyRow($row)) continue;

            $this->total++;

            $data = array_combine($headers, $row);
            if (!empty($data['division'])) {
                $data['division'] = ucwords(strtolower(trim((string) $data['division'])));
            }

            if (empty(trim((string) ($data['division'] ?? '')))) {
                $this->errors[] = "Baris {$lineNumber}: Kolom division wajib diisi.";
                $this->skipped++;
                continue;
            }


            if (empty(trim((string) ($data['division'] ?? '')))) {
                $this->errors[] = "Baris {$lineNumber}: Kolom division wajib diisi.";
                $this->skipped++;
                continue;
            }

            $validator = $this->makeValidator($data);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $this->errors[] = "Baris {$lineNumber}: {$error}";
                }
                $this->skipped++;
                continue;
            }

            Project::updateOrCreate(
                ['project_code' => trim($data['project_code'])],
                [
                    'ingestion_file_id' => $ingestionFileId,
                    'project_name'      => trim($data['project_name']),
                    'division'          => trim($data['division']),
                    'owner'             => isset($data['owner']) ? trim($data['owner']) : null,
                    'contract_value'    => (float) $data['contract_value'],
                    'planned_cost'      => (float) $data['planned_cost'],
                    'actual_cost'       => (float) $data['actual_cost'],
                    'planned_duration'  => (int)   $data['planned_duration'],
                    'actual_duration'   => (int)   $data['actual_duration'],
                    'progress_pct'      => isset($data['progress_pct']) ? (float) $data['progress_pct'] : 100,
                    'project_year'      => (int)   $data['project_year'],
                ]
            );

            $this->imported++;
        }

        return [
            'total'    => $this->total,
            'imported' => $this->imported,
            'skipped'  => $this->skipped,
            'errors'   => $this->errors,
        ];
    }

    private function isTransposed(array $raw): bool
    {
        $colA = array_map(fn($row) => $row[0] ?? null, $raw);
        $colA = array_filter($colA, fn($v) => $v !== null && $v !== '');

        if (empty($colA)) return false;

        $normalized = array_map(function ($v) {
            $v = strtolower(trim((string) $v));
            $v = preg_replace('/[\s\-\(\)\.]+/', '_', $v);
            $v = preg_replace('/[^\w]/', '', $v);
            $v = rtrim($v, '_');
            return self::ALIASES[$v] ?? $v;
        }, $colA);

        $required = self::REQUIRED_COLUMNS;
        $matches  = count(array_intersect($normalized, $required));

        return $matches >= (count($required) * 0.5);
    }

    
    private function transpose(array $raw): array
    {
        if (empty($raw)) return [];

        $colCount = max(array_map('count', $raw));
        $rowCount = count($raw);

        $padded = array_map(
            fn($row) => array_pad(array_values($row), $colCount, null),
            $raw
        );

        $result = [];
        for ($col = 0; $col < $colCount; $col++) {
            $result[] = array_map(fn($row) => $row[$col], $padded);
        }

        return $result;
    }


    private function normalizeHeaders(array $rawHeaders): array
    {
        return array_map(function ($h) {
            $h = strtolower(trim((string) $h));
            $h = preg_replace('/[\s\-\(\)\.]+/', '_', $h); // spasi, dash, kurung, titik → _
            $h = preg_replace('/[^\w]/', '', $h);            // hapus karakter non-word
            $h = rtrim($h, '_');                             // hapus trailing underscore
            return $h;
        }, $rawHeaders);
    }

    private function applyAliases(array $headers): array
    {
        return array_map(fn($h) => self::ALIASES[$h] ?? $h, $headers);
    }

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

    private function isEmptyRow(array $row): bool
    {
        return empty(array_filter($row, fn($cell) => $cell !== null && $cell !== ''));
    }

    private function makeValidator(array $data): \Illuminate\Validation\Validator
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
            'project_year'     => 'required|integer|min:2000|max:2099',
        ], [
            'division.in'           => 'Division harus Infrastructure atau Building.',
            'project_year.required' => 'Project year wajib diisi.',
            'project_year.integer'  => 'Project year harus berupa angka.',
        ]);
    }
}