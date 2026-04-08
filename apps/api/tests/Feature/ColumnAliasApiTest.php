<?php

namespace Tests\Feature;

use App\Models\ColumnAlias;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ColumnAliasApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticateApiUser();
    }

    #[Test]
    public function it_creates_column_alias_and_normalizes_input(): void
    {
        $response = $this->postJson('/api/column-aliases', [
            'alias' => 'Kode ERP Proyek',
            'target_field' => 'project_code',
            'context' => 'project',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.alias', 'kode_erp_proyek')
            ->assertJsonPath('data.target_field', 'project_code')
            ->assertJsonPath('data.context', 'project')
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('column_aliases', [
            'alias' => 'kode_erp_proyek',
            'target_field' => 'project_code',
            'context' => 'project',
        ]);
    }

    #[Test]
    public function it_rejects_invalid_target_field_for_context(): void
    {
        $this->postJson('/api/column-aliases', [
            'alias' => 'Status Vendor',
            'target_field' => 'project_code',
            'context' => 'equipment',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['target_field']);
    }

    #[Test]
    public function it_rejects_alias_that_already_exists_as_builtin(): void
    {
        $this->postJson('/api/column-aliases', [
            'alias' => 'Kode Proyek',
            'target_field' => 'project_code',
            'context' => 'project',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['alias']);
    }

    #[Test]
    public function it_rejects_alias_that_conflicts_with_builtin_target(): void
    {
        $this->postJson('/api/column-aliases', [
            'alias' => 'Kode Proyek',
            'target_field' => 'project_name',
            'context' => 'project',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['alias']);
    }

    #[Test]
    public function it_updates_alias_and_can_reactivate_or_deactivate_it(): void
    {
        $alias = ColumnAlias::create([
            'alias' => 'kode_erp_proyek',
            'target_field' => 'project_code',
            'context' => 'project',
            'is_active' => true,
        ]);

        $this->putJson("/api/column-aliases/{$alias->id}", [
            'alias' => 'Kode Internal SAP',
            'target_field' => 'project_code',
            'is_active' => false,
        ])->assertOk()
            ->assertJsonPath('data.alias', 'kode_internal_sap')
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('column_aliases', [
            'id' => $alias->id,
            'alias' => 'kode_internal_sap',
            'is_active' => false,
        ]);

        $this->patchJson("/api/column-aliases/{$alias->id}", [
            'is_active' => true,
        ])->assertOk()
            ->assertJsonPath('data.is_active', true);
    }

    #[Test]
    public function it_lists_and_filters_column_aliases(): void
    {
        ColumnAlias::create([
            'alias' => 'nama_kontrak',
            'target_field' => 'project_code',
            'context' => 'project',
            'is_active' => true,
        ]);

        ColumnAlias::create([
            'alias' => 'status',
            'target_field' => 'payment_status',
            'context' => 'equipment',
            'is_active' => false,
        ]);

        $this->getJson('/api/column-aliases?context=project')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.context', 'project');

        $this->getJson('/api/column-aliases?active_only=0')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->getJson('/api/column-aliases?q=kontrak')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.alias', 'nama_kontrak');
    }

    #[Test]
    public function it_returns_single_alias_and_soft_deletes_it(): void
    {
        $alias = ColumnAlias::create([
            'alias' => 'nama_kontrak',
            'target_field' => 'project_code',
            'context' => 'project',
            'is_active' => true,
        ]);

        $this->getJson("/api/column-aliases/{$alias->id}")
            ->assertOk()
            ->assertJsonPath('data.alias', 'nama_kontrak');

        $this->deleteJson("/api/column-aliases/{$alias->id}")
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('column_aliases', [
            'id' => $alias->id,
            'is_active' => false,
        ]);
    }
}
