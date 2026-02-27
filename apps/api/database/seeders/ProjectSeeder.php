<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Dummy data dari Project Brief CPIP.
     * CPI dan SPI dihitung otomatis oleh Model::saving().
     *
     * Hasil yang diharapkan:
     * INF-01  CPI=0.857  SPI=0.857  → critical
     * INF-02  CPI=0.967  SPI=1.034  → warning
     * BLD-01  CPI=0.872  SPI=0.818  → critical
     * BLD-02  CPI=1.059  SPI=1.000  → good
     * INF-03  CPI=0.855  SPI=0.833  → critical
     */
    public function run(): void
    {
        $projects = [
            [
                'project_code'     => 'INF-01',
                'project_name'     => 'Tol Semarang Seksi 3',
                'division'         => 'Infrastructure',
                'owner'            => 'BPJT',
                'contract_value'   => 850,
                'planned_cost'     => 780,
                'actual_cost'      => 910,
                'planned_duration' => 24,
                'actual_duration'  => 28,
                'progress_pct'     => 100,
            ],
            [
                'project_code'     => 'INF-02',
                'project_name'     => 'Bendungan Citarum',
                'division'         => 'Infrastructure',
                'owner'            => 'PUPR',
                'contract_value'   => 620,
                'planned_cost'     => 580,
                'actual_cost'      => 600,
                'planned_duration' => 30,
                'actual_duration'  => 29,
                'progress_pct'     => 100,
            ],
            [
                'project_code'     => 'BLD-01',
                'project_name'     => 'RS Regional Surabaya',
                'division'         => 'Building',
                'owner'            => 'Pemprov',
                'contract_value'   => 450,
                'planned_cost'     => 410,
                'actual_cost'      => 470,
                'planned_duration' => 18,
                'actual_duration'  => 22,
                'progress_pct'     => 100,
            ],
            [
                'project_code'     => 'BLD-02',
                'project_name'     => 'Gedung Perkantoran BUMN',
                'division'         => 'Building',
                'owner'            => 'BUMN',
                'contract_value'   => 300,
                'planned_cost'     => 270,
                'actual_cost'      => 255,
                'planned_duration' => 14,
                'actual_duration'  => 14,
                'progress_pct'     => 100,
            ],
            [
                'project_code'     => 'INF-03',
                'project_name'     => 'Jembatan Kalimantan',
                'division'         => 'Infrastructure',
                'owner'            => 'PUPR',
                'contract_value'   => 700,
                'planned_cost'     => 650,
                'actual_cost'      => 760,
                'planned_duration' => 20,
                'actual_duration'  => 24,
                'progress_pct'     => 100,
            ],
        ];

        foreach ($projects as $data) {
            // updateOrCreate agar bisa di-reseed tanpa duplikat
            Project::updateOrCreate(
                ['project_code' => $data['project_code']],
                $data
            );
        }

        $this->command->info('✅ 5 project seeded. KPI dihitung otomatis.');
    }
}