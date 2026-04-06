<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * project_periods — laporan bulanan per proyek (L3)
     *
     * Satu proyek bisa punya banyak periode laporan (time-series).
     * Setiap periode menyimpan snapshot progress dan realisasi biaya
     * pada bulan tertentu, serta metadata laporan (owner, addendum, pagu).
     *
     * Relasi:
     *   projects (1) ──── (many) project_periods
     */
    public function up(): void
    {
        Schema::create('project_periods', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->cascadeOnDelete();

            // ── Identifikasi periode ──────────────────────────────────────
            $table->string('period', 7)->comment('Format YYYY-MM, e.g. 2026-03');
            // Contoh: "Maret 2026" di File 4 → "2026-03"

            // ── Metadata laporan ─────────────────────────────────────────
            $table->string('client_name', 150)->nullable()
                  ->comment('Nama klien/owner, e.g. PT Halo Bandung tbk.');
            $table->string('project_manager', 100)->nullable();
            $table->string('report_source', 50)->nullable()
                  ->comment('Sumber: manual | file_import');
            $table->foreignId('ingestion_file_id')
                  ->nullable()
                  ->constrained('ingestion_files')
                  ->nullOnDelete();

            // ── Progress fisik ────────────────────────────────────────────
            // Dari File 4: "s/d bulan lalu: 45.2% | bulan ini: 5.8% | total: 51.0%"
            $table->decimal('progress_prev_pct', 6, 2)->nullable()
                  ->comment('Progress s/d bulan lalu (%)');
            $table->decimal('progress_this_pct', 6, 2)->nullable()
                  ->comment('Progress bulan ini (%)');
            $table->decimal('progress_total_pct', 6, 2)->nullable()
                  ->comment('Total progress kumulatif (%)');

            // ── Nilai kontrak & pagu ──────────────────────────────────────
            // Dari File 5 COVER: contract_value, addendum, total_pagu
            $table->decimal('contract_value', 20, 2)->nullable()
                  ->comment('Nilai kontrak pada periode ini (IDR)');
            $table->decimal('addendum_value', 20, 2)->nullable()
                  ->comment('Nilai addendum, bisa negatif');
            $table->decimal('total_pagu', 20, 2)->nullable()
                  ->comment('contract_value + addendum_value');

            // ── HPP summary ───────────────────────────────────────────────
            // Aggregate dari work_items, disimpan juga di sini untuk query cepat
            $table->decimal('hpp_plan_total', 20, 2)->nullable()
                  ->comment('Total HPP rencana dari semua item pekerjaan');
            $table->decimal('hpp_actual_total', 20, 2)->nullable()
                  ->comment('Total HPP realisasi (ITD = inception to date)');
            $table->decimal('hpp_deviation', 20, 2)->nullable()
                  ->comment('hpp_plan_total - hpp_actual_total');

            $table->timestamps();

            // Satu proyek hanya boleh punya satu laporan per bulan
            $table->unique(['project_id', 'period'], 'uq_project_period');

            // Index untuk query filter per tahun/bulan
            $table->index('period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_periods');
    }
};