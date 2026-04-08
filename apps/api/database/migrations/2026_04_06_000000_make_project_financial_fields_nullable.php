<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Make financial and operational project fields nullable.
 *
 * Rationale: not all Excel formats provide every field upfront.
 * A null value is semantically cleaner than a default of 0 or 1,
 * which could be mistaken for real data.
 * CPI/SPI will also be null when inputs are unavailable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('division', 100)->nullable()->change();
            $table->decimal('contract_value', 15, 2)->nullable()->change();
            $table->decimal('planned_cost', 15, 2)->nullable()->change();
            $table->decimal('actual_cost', 15, 2)->nullable()->change();
            $table->integer('planned_duration')->nullable()->change();
            $table->integer('actual_duration')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('division', 100)->nullable(false)->default('')->change();
            $table->decimal('contract_value', 15, 2)->nullable(false)->default(0)->change();
            $table->decimal('planned_cost', 15, 2)->nullable(false)->default(0)->change();
            $table->decimal('actual_cost', 15, 2)->nullable(false)->default(0)->change();
            $table->integer('planned_duration')->nullable(false)->default(1)->change();
            $table->integer('actual_duration')->nullable(false)->default(1)->change();
        });
    }
};
