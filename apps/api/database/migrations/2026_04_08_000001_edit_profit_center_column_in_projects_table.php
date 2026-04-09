<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Edit profit_center column in projects table
 *
 * Changes:
 * - Rename column: profit_center_code → profit_center (if still exists)
 * - Increase length: varchar(50) → varchar(255) to accommodate longer names
 * - Update comment to reflect new usage (division names instead of codes)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Only rename if the old column still exists
            if (Schema::hasColumn('projects', 'profit_center_code')) {
                $table->renameColumn('profit_center_code', 'profit_center');
            }

            // Ensure profit_center is varchar(255)
            $table->string('profit_center', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('profit_center_code', 50)->nullable()->change();
            $table->renameColumn('profit_center', 'profit_center_code');
        });
    }
};
