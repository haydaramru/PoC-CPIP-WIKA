<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectDirectCost;
use App\Models\ProjectIndirectCost;
use App\Models\ProjectOtherCost;
use App\Models\ProjectProfitLoss;
use App\Models\ProjectSale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProjectProfitLossApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticateApiUser();
    }

    private function makeProject(array $overrides = []): Project
    {
        return Project::create(array_merge([
            'project_code' => 'PL-01',
            'project_name' => 'Profit Loss Project',
            'division' => 'Infrastructure',
            'sbu' => 'SBU Profit Loss',
            'owner' => 'Owner Profit Loss',
            'contract_type' => 'Lumpsum',
            'contract_value' => 500,
            'planned_cost' => 400,
            'actual_cost' => 450,
            'planned_duration' => 12,
            'actual_duration' => 14,
            'progress_pct' => 100,
        ], $overrides));
    }

    #[Test]
    public function it_returns_project_profit_loss_for_level_three(): void
    {
        $project = $this->makeProject();

        ProjectProfitLoss::create([
            'project_id' => $project->id,
            'beban_pph_final' => 25,
            'laba_kotor' => 590,
            'lsp' => 5,
        ]);

        ProjectSale::create([
            'project_id' => $project->id,
            'penjualan' => 1000,
        ]);

        ProjectDirectCost::create([
            'project_id' => $project->id,
            'material' => 100,
            'upah' => 80,
            'alat' => 60,
            'subkon' => 40,
        ]);

        ProjectIndirectCost::create([
            'project_id' => $project->id,
            'fasilitas' => 30,
            'sekretariat' => 20,
            'kendaraan' => 10,
            'personalia' => 15,
            'keuangan' => 12,
            'umum' => 8,
        ]);

        ProjectOtherCost::create([
            'project_id' => $project->id,
            'biaya_pemeliharaan' => 6,
            'risiko' => 4,
        ]);

        $response = $this->getJson("/api/projects/{$project->id}/profit-loss");

        $response->assertOk()
            ->assertJsonPath('data.project_name', 'Profit Loss Project')
            ->assertJsonPath('data.sbu', 'SBU Profit Loss')
            ->assertJsonPath('data.owner', 'Owner Profit Loss')
            ->assertJsonPath('data.contract_type', 'Lumpsum')
            ->assertJsonPath('data.penjualan', 1000)
            ->assertJsonPath('data.biaya_langsung.material', 100)
            ->assertJsonPath('data.biaya_langsung.upah', 80)
            ->assertJsonPath('data.biaya_langsung.alat', 60)
            ->assertJsonPath('data.biaya_langsung.subkon', 40)
            ->assertJsonPath('data.biaya_tak_langsung.fasilitas', 30)
            ->assertJsonPath('data.biaya_tak_langsung.sekretariat', 20)
            ->assertJsonPath('data.biaya_tak_langsung.kendaraan', 10)
            ->assertJsonPath('data.biaya_tak_langsung.personalia', 15)
            ->assertJsonPath('data.biaya_tak_langsung.keuangan', 12)
            ->assertJsonPath('data.biaya_tak_langsung.umum', 8)
            ->assertJsonPath('data.biaya_lain_lain.biaya_pemeliharaan', 6)
            ->assertJsonPath('data.biaya_lain_lain.risiko', 4)
            ->assertJsonPath('data.beban_pph_final', 25)
            ->assertJsonPath('data.laba_kotor', 590)
            ->assertJsonPath('data.lsp', 5);
    }

    #[Test]
    public function it_returns_zeroes_when_project_has_no_profit_loss_rows(): void
    {
        $project = $this->makeProject();

        $response = $this->getJson("/api/projects/{$project->id}/profit-loss");

        $response->assertOk()
            ->assertJsonPath('data.project_name', 'Profit Loss Project')
            ->assertJsonPath('data.penjualan', 0)
            ->assertJsonPath('data.biaya_langsung.material', 0)
            ->assertJsonPath('data.biaya_langsung.upah', 0)
            ->assertJsonPath('data.biaya_langsung.alat', 0)
            ->assertJsonPath('data.biaya_langsung.subkon', 0)
            ->assertJsonPath('data.biaya_tak_langsung.fasilitas', 0)
            ->assertJsonPath('data.biaya_tak_langsung.sekretariat', 0)
            ->assertJsonPath('data.biaya_tak_langsung.kendaraan', 0)
            ->assertJsonPath('data.biaya_tak_langsung.personalia', 0)
            ->assertJsonPath('data.biaya_tak_langsung.keuangan', 0)
            ->assertJsonPath('data.biaya_tak_langsung.umum', 0)
            ->assertJsonPath('data.biaya_lain_lain.biaya_pemeliharaan', 0)
            ->assertJsonPath('data.biaya_lain_lain.risiko', 0)
            ->assertJsonPath('data.beban_pph_final', 0)
            ->assertJsonPath('data.laba_kotor', 0)
            ->assertJsonPath('data.lsp', 0);
    }

    #[Test]
    public function deleting_a_project_cascades_its_profit_loss_rows(): void
    {
        $project = $this->makeProject();

        ProjectProfitLoss::create(['project_id' => $project->id, 'laba_kotor' => 590]);
        ProjectSale::create(['project_id' => $project->id, 'penjualan' => 1000]);
        ProjectDirectCost::create(['project_id' => $project->id, 'material' => 100]);
        ProjectIndirectCost::create(['project_id' => $project->id, 'fasilitas' => 30]);
        ProjectOtherCost::create(['project_id' => $project->id, 'risiko' => 4]);

        $project->delete();

        foreach ([
            'project_profit_loss',
            'project_sales',
            'project_direct_cost',
            'project_indirect_cost',
            'project_other_cost',
        ] as $table) {
            $this->assertDatabaseMissing($table, ['project_id' => $project->id]);
        }
    }
}
