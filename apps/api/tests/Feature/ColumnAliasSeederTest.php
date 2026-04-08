<?php

namespace Tests\Feature;

use App\Models\ColumnAlias;
use App\Services\WorkbookFieldMapper;
use Database\Seeders\ColumnAliasSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ColumnAliasSeederTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_seeds_all_builtin_aliases_from_workbook_field_mapper(): void
    {
        $this->seed(ColumnAliasSeeder::class);

        $expectedRows = WorkbookFieldMapper::builtinAliasesForSeeding();

        foreach ($expectedRows as $row) {
            $this->assertDatabaseHas('column_aliases', [
                'alias' => $row['alias'],
                'target_field' => $row['target_field'],
                'context' => $row['context'],
                'is_active' => true,
            ]);
        }

        $this->assertSame(count($expectedRows), ColumnAlias::count());
    }
}
