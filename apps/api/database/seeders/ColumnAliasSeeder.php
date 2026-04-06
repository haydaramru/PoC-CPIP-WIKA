<?php

namespace Database\Seeders;

use App\Models\ColumnAlias;
use App\Services\WorkbookFieldMapper;
use Illuminate\Database\Seeder;

class ColumnAliasSeeder extends Seeder
{
    public function run(): void
    {
        foreach (WorkbookFieldMapper::builtinAliasesForSeeding() as $aliasRow) {
            ColumnAlias::updateOrCreate(
                [
                    'alias' => $aliasRow['alias'],
                    'context' => $aliasRow['context'],
                ],
                [
                    'target_field' => $aliasRow['target_field'],
                    'is_active' => true,
                    'created_by' => null,
                ]
            );
        }
    }
}
