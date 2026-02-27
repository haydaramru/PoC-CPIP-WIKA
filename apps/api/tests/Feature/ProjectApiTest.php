<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Helper: buat project dummy
    // =========================================================================

    private function makeProject(array $overrides = []): Project
    {
        return Project::create(array_merge([
            'project_code'     => 'TST-01',
            'project_name'     => 'Test Project',
            'division'         => 'Infrastructure',
            'owner'            => 'Test Owner',
            'contract_value'   => 500,
            'planned_cost'     => 400,
            'actual_cost'      => 450,
            'planned_duration' => 12,
            'actual_duration'  => 14,
            'progress_pct'     => 100,
        ], $overrides));
    }

    // =========================================================================
    // GET /api/projects
    // =========================================================================

    /** @test */
    public function it_returns_empty_project_list(): void
    {
        $response = $this->getJson('/api/projects');

        $response->assertOk()
                 ->assertJsonStructure(['data', 'meta'])
                 ->assertJsonPath('meta.total', 0);
    }

    /** @test */
    public function it_returns_all_projects(): void
    {
        $this->makeProject(['project_code' => 'TST-01']);
        $this->makeProject(['project_code' => 'TST-02', 'project_name' => 'Another Project']);

        $response = $this->getJson('/api/projects');

        $response->assertOk()
                 ->assertJsonPath('meta.total', 2)
                 ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function it_filters_projects_by_division(): void
    {
        $this->makeProject(['project_code' => 'INF-01', 'division' => 'Infrastructure']);
        $this->makeProject(['project_code' => 'BLD-01', 'division' => 'Building', 'planned_cost' => 300, 'actual_cost' => 280]);

        $response = $this->getJson('/api/projects?division=Infrastructure');

        $response->assertOk()
                 ->assertJsonPath('meta.total', 1)
                 ->assertJsonPath('data.0.division', 'Infrastructure');
    }

    /** @test */
    public function it_sorts_projects_by_cpi_ascending(): void
    {
        // Project 1: CPI = 400/450 = 0.888 (worse)
        $this->makeProject(['project_code' => 'TST-01', 'planned_cost' => 400, 'actual_cost' => 450]);
        // Project 2: CPI = 270/255 = 1.059 (better)
        $this->makeProject(['project_code' => 'TST-02', 'planned_cost' => 270, 'actual_cost' => 255]);

        $response = $this->getJson('/api/projects?sort_by=cpi&sort_dir=asc');

        $response->assertOk();
        $data = $response->json('data');

        // CPI terendah harus di index 0
        $this->assertLessThan(
            (float) $data[1]['cpi'],
            (float) $data[0]['cpi']
        );
    }

    /** @test */
    public function it_returns_correct_meta_overbudget_count(): void
    {
        // Overbudget: actual > planned → CPI < 1
        $this->makeProject(['project_code' => 'TST-01', 'planned_cost' => 400, 'actual_cost' => 450]); // overbudget
        $this->makeProject(['project_code' => 'TST-02', 'planned_cost' => 270, 'actual_cost' => 255]); // under budget

        $response = $this->getJson('/api/projects');

        $response->assertOk()
                 ->assertJsonPath('meta.overbudget_count', 1);
    }

    // =========================================================================
    // GET /api/projects/summary
    // =========================================================================

    /** @test */
    public function it_returns_summary_with_zero_when_no_projects(): void
    {
        $response = $this->getJson('/api/projects/summary');

        $response->assertOk()
                 ->assertJsonPath('total_projects', 0);
    }

    /** @test */
    public function it_returns_correct_summary_data(): void
    {
        $this->makeProject(['project_code' => 'INF-01', 'planned_cost' => 780, 'actual_cost' => 910, 'planned_duration' => 24, 'actual_duration' => 28]);
        $this->makeProject(['project_code' => 'BLD-01', 'division' => 'Building', 'planned_cost' => 270, 'actual_cost' => 255, 'planned_duration' => 14, 'actual_duration' => 14]);

        $response = $this->getJson('/api/projects/summary');

        $response->assertOk()
                 ->assertJsonPath('total_projects', 2)
                 ->assertJsonStructure([
                     'total_projects',
                     'avg_cpi',
                     'avg_spi',
                     'overbudget_count',
                     'delay_count',
                     'overbudget_pct',
                     'delay_pct',
                     'by_division',
                     'status_breakdown',
                 ]);
    }

    /** @test */
    public function it_groups_summary_by_division(): void
    {
        $this->makeProject(['project_code' => 'INF-01', 'division' => 'Infrastructure']);
        $this->makeProject(['project_code' => 'BLD-01', 'division' => 'Building', 'planned_cost' => 300, 'actual_cost' => 280]);

        $response = $this->getJson('/api/projects/summary');

        $response->assertOk()
                 ->assertJsonStructure(['by_division' => ['Infrastructure', 'Building']]);
    }

    // =========================================================================
    // GET /api/projects/{id}
    // =========================================================================

    /** @test */
    public function it_returns_project_detail(): void
    {
        $project = $this->makeProject();

        $response = $this->getJson("/api/projects/{$project->id}");

        $response->assertOk()
                 ->assertJsonPath('data.project_code', 'TST-01')
                 ->assertJsonPath('data.project_name', 'Test Project')
                 ->assertJsonStructure(['data' => [
                     'id', 'project_code', 'project_name', 'division',
                     'cpi', 'spi', 'status',
                 ]]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_project(): void
    {
        $this->getJson('/api/projects/9999')->assertNotFound();
    }

    // =========================================================================
    // POST /api/projects
    // =========================================================================

    /** @test */
    public function it_creates_project_and_calculates_kpi(): void
    {
        $payload = [
            'project_code'     => 'NEW-01',
            'project_name'     => 'New Project',
            'division'         => 'Building',
            'contract_value'   => 300,
            'planned_cost'     => 270,
            'actual_cost'      => 255,
            'planned_duration' => 14,
            'actual_duration'  => 14,
        ];

        $response = $this->postJson('/api/projects', $payload);

        $response->assertCreated()
                 ->assertJsonPath('data.project_code', 'NEW-01')
                 ->assertJsonPath('data.status', 'good'); // CPI > 1, SPI = 1

        $this->assertDatabaseHas('projects', ['project_code' => 'NEW-01']);
    }

    /** @test */
    public function it_validates_required_fields_on_create(): void
    {
        $response = $this->postJson('/api/projects', []);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors([
                     'project_code',
                     'project_name',
                     'division',
                     'contract_value',
                     'planned_cost',
                     'actual_cost',
                     'planned_duration',
                     'actual_duration',
                 ]);
    }

    /** @test */
    public function it_validates_division_must_be_valid(): void
    {
        $response = $this->postJson('/api/projects', [
            'project_code'     => 'TST-01',
            'project_name'     => 'Test',
            'division'         => 'InvalidDivision',
            'contract_value'   => 100,
            'planned_cost'     => 100,
            'actual_cost'      => 100,
            'planned_duration' => 12,
            'actual_duration'  => 12,
        ]);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['division']);
    }

    /** @test */
    public function it_prevents_duplicate_project_code(): void
    {
        $this->makeProject(['project_code' => 'DUP-01']);

        $response = $this->postJson('/api/projects', [
            'project_code'     => 'DUP-01',
            'project_name'     => 'Duplicate',
            'division'         => 'Building',
            'contract_value'   => 100,
            'planned_cost'     => 100,
            'actual_cost'      => 100,
            'planned_duration' => 12,
            'actual_duration'  => 12,
        ]);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['project_code']);
    }

    // =========================================================================
    // PUT /api/projects/{id}
    // =========================================================================

    /** @test */
    public function it_updates_project_and_recalculates_kpi(): void
    {
        $project = $this->makeProject([
            'planned_cost'   => 400,
            'actual_cost'    => 450, // overbudget
        ]);

        // Update: sekarang under budget
        $response = $this->putJson("/api/projects/{$project->id}", [
            'project_code'     => 'TST-01',
            'project_name'     => 'Test Project',
            'division'         => 'Infrastructure',
            'contract_value'   => 500,
            'planned_cost'     => 400,
            'actual_cost'      => 380, // under budget sekarang
            'planned_duration' => 12,
            'actual_duration'  => 14,
        ]);

        $response->assertOk();
        $this->assertGreaterThan(1, (float) $response->json('data.cpi'));
    }

    // =========================================================================
    // DELETE /api/projects/{id}
    // =========================================================================

    /** @test */
    public function it_deletes_a_project(): void
    {
        $project = $this->makeProject();

        $this->deleteJson("/api/projects/{$project->id}")
             ->assertOk()
             ->assertJsonPath('message', 'Project berhasil dihapus.');

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_project(): void
    {
        $this->deleteJson('/api/projects/9999')->assertNotFound();
    }

    // =========================================================================
    // KPI auto-calculation via Model
    // =========================================================================

    /** @test */
    public function it_auto_calculates_cpi_on_create(): void
    {
        $project = $this->makeProject([
            'planned_cost' => 780,
            'actual_cost'  => 910,
        ]);

        $this->assertEqualsWithDelta(0.8571, (float) $project->cpi, 0.0001);
    }

    /** @test */
    public function it_auto_sets_status_critical_when_cpi_below_0_9(): void
    {
        $project = $this->makeProject([
            'planned_cost'     => 780,
            'actual_cost'      => 910,  // CPI = 0.857
            'planned_duration' => 24,
            'actual_duration'  => 28,   // SPI = 0.857
        ]);

        $this->assertEquals('critical', $project->status);
    }

    /** @test */
    public function it_auto_sets_status_good_for_gedung_bumn_data(): void
    {
        $project = $this->makeProject([
            'project_code'     => 'BLD-02',
            'planned_cost'     => 270,
            'actual_cost'      => 255,  // CPI = 1.059
            'planned_duration' => 14,
            'actual_duration'  => 14,   // SPI = 1.0
        ]);

        $this->assertEquals('good', $project->status);
    }
}