<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ProjectUploadTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Helper: buat file Excel sementara
    // =========================================================================

    private function makeExcelFile(array $headers, array $rows): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Tulis header di baris 1
        // Coordinate::stringFromColumnIndex() convert angka ke huruf kolom (1→A, 2→B, dst)
        foreach ($headers as $col => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
            $sheet->setCellValue("{$colLetter}1", $header);
        }

        // Tulis data mulai baris 2
        foreach ($rows as $rowIdx => $row) {
            foreach ($row as $col => $value) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
                $sheet->setCellValue("{$colLetter}" . ($rowIdx + 2), $value);
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'cpip_test_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }

    private function makeUploadedFile(string $path): UploadedFile
    {
        return new UploadedFile(
            path: $path,
            originalName: 'test_projects.xlsx',
            mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            error: UPLOAD_ERR_OK,
            test: true,
        );
    }

    // =========================================================================
    // Validation Tests
    // =========================================================================

    /** @test */
    public function it_rejects_upload_without_file(): void
    {
        $this->postJson('/api/projects/upload', [])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function it_rejects_non_excel_file(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $this->postJson('/api/projects/upload', ['file' => $file])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['file']);
    }

    /** @test */
    public function it_rejects_file_over_5mb(): void
    {
        $file = UploadedFile::fake()->create('large.xlsx', 6000, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->postJson('/api/projects/upload', ['file' => $file])
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['file']);
    }

    // =========================================================================
    // Import Success Tests
    // =========================================================================

    /** @test */
    public function it_imports_projects_from_valid_excel(): void
    {
        $headers = ['project_code', 'project_name', 'division', 'contract_value', 'planned_cost', 'actual_cost', 'planned_duration', 'actual_duration'];
        $rows    = [
            ['INF-01', 'Tol Semarang Seksi 3', 'Infrastructure', 850, 780, 910, 24, 28],
            ['BLD-02', 'Gedung Perkantoran BUMN', 'Building', 300, 270, 255, 14, 14],
        ];

        $path = $this->makeExcelFile($headers, $rows);
        $file = $this->makeUploadedFile($path);

        $response = $this->postJson('/api/projects/upload', ['file' => $file]);

        $response->assertOk()
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('imported', 2)
                 ->assertJsonPath('skipped', 0);

        $this->assertDatabaseHas('projects', ['project_code' => 'INF-01']);
        $this->assertDatabaseHas('projects', ['project_code' => 'BLD-02']);

        @unlink($path);
    }

    /** @test */
    public function it_calculates_kpi_after_import(): void
    {
        $headers = ['project_code', 'project_name', 'contract_value', 'planned_cost', 'actual_cost', 'planned_duration', 'actual_duration'];
        $rows    = [
            ['BLD-02', 'Gedung BUMN', 300, 270, 255, 14, 14],
        ];

        $path = $this->makeExcelFile($headers, $rows);
        $file = $this->makeUploadedFile($path);

        $this->postJson('/api/projects/upload', ['file' => $file])->assertOk();

        $project = \App\Models\Project::where('project_code', 'BLD-02')->first();

        $this->assertNotNull($project);
        $this->assertEqualsWithDelta(1.059, (float) $project->cpi, 0.001);
        $this->assertEquals(1.0, (float) $project->spi);
        $this->assertEquals('good', $project->status);

        @unlink($path);
    }

    /** @test */
    public function it_accepts_excel_headers_with_spaces_and_units(): void
    {
        // Simulasi header dari Excel user asli
        $headers = [
            'project_code', 'project_name',
            'Contract Value (M)', 'Planned Duration (month)',
            'Actual Duration', 'Planned Cost (M)', 'Actual Cost (M)',
            'Progress %', 'Owner',
        ];
        $rows = [
            ['INF-01', 'Tol Semarang', 850, 24, 28, 780, 910, 100, 'BPJT'],
        ];

        $path = $this->makeExcelFile($headers, $rows);
        $file = $this->makeUploadedFile($path);

        $response = $this->postJson('/api/projects/upload', ['file' => $file]);

        $response->assertOk()
                 ->assertJsonPath('imported', 1)
                 ->assertJsonPath('skipped', 0);

        $this->assertDatabaseHas('projects', ['project_code' => 'INF-01']);

        @unlink($path);
    }

    /** @test */
    public function it_upserts_existing_project_on_reimport(): void
    {
        // Import pertama
        $headers = ['project_code', 'project_name', 'contract_value', 'planned_cost', 'actual_cost', 'planned_duration', 'actual_duration'];
        $path    = $this->makeExcelFile($headers, [['INF-01', 'Tol Semarang', 850, 780, 910, 24, 28]]);
        $this->postJson('/api/projects/upload', ['file' => $this->makeUploadedFile($path)])->assertOk();
        @unlink($path);

        // Import kedua dengan data berbeda (actual_cost diubah)
        $path = $this->makeExcelFile($headers, [['INF-01', 'Tol Semarang Updated', 850, 780, 850, 24, 24]]);
        $this->postJson('/api/projects/upload', ['file' => $this->makeUploadedFile($path)])->assertOk();
        @unlink($path);

        // Harus tetap 1 record, data ter-update
        $this->assertDatabaseCount('projects', 1);
        $project = \App\Models\Project::where('project_code', 'INF-01')->first();
        $this->assertEquals('Tol Semarang Updated', $project->project_name);
        $this->assertEquals(1.0, (float) $project->cpi); // 780/850 ≠ 1 tapi mendekati
    }

    // =========================================================================
    // Import Error Handling Tests
    // =========================================================================

    /** @test */
    public function it_returns_error_when_required_columns_missing(): void
    {
        // Excel tanpa kolom planned_cost dan actual_cost
        $headers = ['project_code', 'project_name', 'division'];
        $rows    = [['INF-01', 'Tol Semarang', 'Infrastructure']];

        $path = $this->makeExcelFile($headers, $rows);
        $file = $this->makeUploadedFile($path);

        $response = $this->postJson('/api/projects/upload', ['file' => $file]);

        $response->assertUnprocessable()
                 ->assertJsonPath('success', false)
                 ->assertJsonFragment(['success' => false]);

        @unlink($path);
    }

    /** @test */
    public function it_skips_rows_with_invalid_division_and_reports_error(): void
    {
        $headers = ['project_code', 'project_name', 'division', 'contract_value', 'planned_cost', 'actual_cost', 'planned_duration', 'actual_duration'];
        $rows    = [
            ['GOOD-01', 'Valid Project',   'Infrastructure', 500, 400, 450, 12, 14], // valid
            ['BAD-01',  'Invalid Project', 'InvalidDiv',     500, 400, 450, 12, 14], // invalid division
        ];

        $path = $this->makeExcelFile($headers, $rows);
        $file = $this->makeUploadedFile($path);

        $response = $this->postJson('/api/projects/upload', ['file' => $file]);

        $response->assertOk()
                 ->assertJsonPath('imported', 1)
                 ->assertJsonPath('skipped', 1);

        $errors = $response->json('errors');
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Baris 3', $errors[0]);

        $this->assertDatabaseHas('projects',    ['project_code' => 'GOOD-01']);
        $this->assertDatabaseMissing('projects', ['project_code' => 'BAD-01']);

        @unlink($path);
    }

    /** @test */
    public function it_handles_empty_excel_file(): void
    {
        $path = $this->makeExcelFile([], []);
        $file = $this->makeUploadedFile($path);

        $this->postJson('/api/projects/upload', ['file' => $file])
             ->assertUnprocessable();

        @unlink($path);
    }
}