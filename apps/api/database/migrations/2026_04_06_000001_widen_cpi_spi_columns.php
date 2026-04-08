<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Widen cpi/spi columns from decimal(10,4) to decimal(20,4).
 *
 * The original precision(10,4) allows a max absolute value of 999999.9999.
 * When input data has grossly mismatched scales (e.g. planned_cost in full IDR
 * vs actual_cost parsed as a small number), the calculated ratio can overflow.
 * decimal(20,4) raises the ceiling to 10^16, which is effectively unbounded for
 * any realistic KPI value.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('cpi', 20, 4)->nullable()->change();
            $table->decimal('spi', 20, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('cpi', 10, 4)->nullable()->change();
            $table->decimal('spi', 10, 4)->nullable()->change();
        });
    }
};
