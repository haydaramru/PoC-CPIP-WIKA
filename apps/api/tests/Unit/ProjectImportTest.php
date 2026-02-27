<?php

namespace Tests\Unit;

use App\Imports\ProjectImport;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ProjectImportTest extends TestCase
{
    private ProjectImport $importer;
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importer   = new ProjectImport();
        $this->reflection = new ReflectionClass($this->importer);
    }

    /**
     * Helper: akses private method via Reflection
     */
    private function callPrivate(string $method, array $args = []): mixed
    {
        $m = $this->reflection->getMethod($method);
        $m->setAccessible(true);
        return $m->invoke($this->importer, ...$args);
    }

    // =========================================================================
    // normalizeHeaders Tests
    // =========================================================================

    /** @test */
    public function it_lowercases_headers(): void
    {
        $result = $this->callPrivate('normalizeHeaders', [['Project Code', 'Division']]);
        $this->assertEquals(['project_code', 'division'], $result);
    }

    /** @test */
    public function it_converts_spaces_to_underscores(): void
    {
        $result = $this->callPrivate('normalizeHeaders', [['Contract Value', 'Planned Duration']]);
        $this->assertEquals(['contract_value', 'planned_duration'], $result);
    }

    /** @test */
    public function it_converts_dashes_to_underscores(): void
    {
        $result = $this->callPrivate('normalizeHeaders', [['planned-cost', 'actual-cost']]);
        $this->assertEquals(['planned_cost', 'actual_cost'], $result);
    }

    /** @test */
    public function it_removes_special_characters(): void
    {
        // "Progress %" → "progress_" (% dihapus)
        $result = $this->callPrivate('normalizeHeaders', [['Progress %']]);
        $this->assertEquals(['progress_'], $result);
    }

    /** @test */
    public function it_trims_whitespace(): void
    {
        $result = $this->callPrivate('normalizeHeaders', [['  project_code  ']]);
        $this->assertEquals(['project_code'], $result);
    }

    // =========================================================================
    // applyAliases Tests
    // =========================================================================

    /** @test */
    public function it_maps_contract_value_m_to_contract_value(): void
    {
        $result = $this->callPrivate('applyAliases', [['contract_value_m']]);
        $this->assertEquals(['contract_value'], $result);
    }

    /** @test */
    public function it_maps_planned_cost_m_to_planned_cost(): void
    {
        $result = $this->callPrivate('applyAliases', [['planned_cost_m']]);
        $this->assertEquals(['planned_cost'], $result);
    }

    /** @test */
    public function it_maps_actual_cost_m_to_actual_cost(): void
    {
        $result = $this->callPrivate('applyAliases', [['actual_cost_m']]);
        $this->assertEquals(['actual_cost'], $result);
    }

    /** @test */
    public function it_maps_planned_duration_month_to_planned_duration(): void
    {
        $result = $this->callPrivate('applyAliases', [['planned_duration_month']]);
        $this->assertEquals(['planned_duration'], $result);
    }

    /** @test */
    public function it_maps_progress_underscore_to_progress_pct(): void
    {
        $result = $this->callPrivate('applyAliases', [['progress_']]);
        $this->assertEquals(['progress_pct'], $result);
    }

    /** @test */
    public function it_maps_indonesian_aliases_correctly(): void
    {
        $headers = ['kode_project', 'nama_project', 'divisi', 'nilai_kontrak'];
        $result  = $this->callPrivate('applyAliases', [$headers]);

        $this->assertEquals('project_code',   $result[0]);
        $this->assertEquals('project_name',   $result[1]);
        $this->assertEquals('division',        $result[2]);
        $this->assertEquals('contract_value',  $result[3]);
    }

    /** @test */
    public function it_keeps_unknown_headers_unchanged(): void
    {
        $result = $this->callPrivate('applyAliases', [['some_unknown_column']]);
        $this->assertEquals(['some_unknown_column'], $result);
    }

    // =========================================================================
    // Full pipeline: normalize + alias
    // =========================================================================

    /** @test */
    public function it_correctly_processes_real_excel_headers_from_brief(): void
    {
        // Ini adalah header yang terbaca dari Excel asli user
        $rawHeaders = [
            'project_code',
            'project_name',
            'Contract Value (M)',
            'Planned Duration (month)',
            'Actual Duration',
            'Planned Cost (M)',
            'Actual Cost (M)',
            'Progress %',
            'Owner',
        ];

        $normalized = $this->callPrivate('normalizeHeaders', [$rawHeaders]);
        $aliased    = $this->callPrivate('applyAliases', [$normalized]);

        $this->assertEquals('project_code',      $aliased[0]);
        $this->assertEquals('project_name',      $aliased[1]);
        $this->assertEquals('contract_value',    $aliased[2]);
        $this->assertEquals('planned_duration',  $aliased[3]);
        $this->assertEquals('actual_duration',   $aliased[4]);
        $this->assertEquals('planned_cost',      $aliased[5]);
        $this->assertEquals('actual_cost',       $aliased[6]);
        $this->assertEquals('progress_pct',      $aliased[7]);
        $this->assertEquals('owner',             $aliased[8]);
    }

    // =========================================================================
    // isEmptyRow Tests
    // =========================================================================

    /** @test */
    public function it_detects_empty_rows(): void
    {
        $this->assertTrue(
            $this->callPrivate('isEmptyRow', [[null, null, '', null]])
        );
    }

    /** @test */
    public function it_detects_non_empty_rows(): void
    {
        $this->assertFalse(
            $this->callPrivate('isEmptyRow', [['INF-01', 'Tol Semarang', null]])
        );
    }
}