<?php

namespace Tests\Feature;

use App\Exceptions\ImportValidationException;
use App\Models\ColumnAlias;
use App\Services\ProjectImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

class ProjectImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeExcelFile(array $headers, array $rows): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($headers as $col => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
            $sheet->setCellValue("{$colLetter}1", $header);
        }

        foreach ($rows as $rowIdx => $row) {
            foreach ($row as $col => $value) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
                $sheet->setCellValue("{$colLetter}" . ($rowIdx + 2), $value);
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'cpip_import_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }

    #[Test]
    public function it_uses_database_aliases_for_project_import(): void
    {
        ColumnAlias::create([
            'alias' => 'kode_unik_proyek',
            'target_field' => 'project_code',
            'context' => 'project',
            'is_active' => true,
        ]);

        $importer = new ProjectImport();
        $reflection = new ReflectionClass($importer);
        $method = $reflection->getMethod('applyAliases');
        $method->setAccessible(true);

        $resolved = $method->invoke($importer, ['kode_unik_proyek', 'project_name']);

        $this->assertSame(['project_code', 'project_name'], $resolved);
    }

    #[Test]
    public function it_reports_unrecognized_columns_when_required_headers_cannot_be_mapped(): void
    {
        $path = $this->makeExcelFile([
            'project_code',
            'project_name',
            'division',
            'contract_value',
            'biaya_rencana_2',
            'tgl_mulai',
            'project_year',
            'planned_duration',
            'actual_duration',
        ], [
            ['INF-01', 'Tol Semarang', 'Infrastructure', 850, 780, '2026-03-01', 2026, 24, 28],
        ]);

        try {
            $this->expectException(ImportValidationException::class);
            $this->expectExceptionMessage('2 kolom wajib tidak dikenali');

            (new ProjectImport())->import($path);
        } catch (ImportValidationException $e) {
            $this->assertSame(['biaya_rencana_2', 'tgl_mulai'], $e->unrecognizedColumns());
            $this->assertSame('Tambahkan alias di halaman Column Mapping', $e->suggestion());
            throw $e;
        } finally {
            @unlink($path);
        }
    }
}
