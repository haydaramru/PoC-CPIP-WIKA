<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * project_progress_curves — data S-Curve per minggu (L6)
     *
     * Time-series mingguan plan vs actual untuk render S-Curve chart.
     * Terikat langsung ke projects (bukan ke periods) karena granularitas
     * per minggu tidak align dengan periode laporan bulanan.
     *
     * Dari File 5 (CURVA_S_PROGRESS):
     *   Minggu Ke- | Rencana (%) | Realisasi (%) | Deviasi (%) | Keterangan
     *
     * Contoh data:
     *   Minggu 12 → rencana 42.50%, realisasi 38.15%, deviasi -4.35% (Critical)
     *   Minggu 13 → rencana 45.10%, realisasi 40.20%, deviasi -4.90% (Material Delay)
     *
     * Relasi:
     *   projects (1) ──── (many) project_progress_curves
     */
    public function up(): void
    {
        Schema::create('project_progress_curves', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->cascadeOnDelete();

            // ── Identifikasi minggu ───────────────────────────────────────
            $table->unsignedSmallInteger('week_number')
                  ->comment('Nomor minggu proyek, e.g. 12, 13');

            // Opsional: tanggal konkret untuk x-axis chart yang lebih akurat
            $table->date('week_date')->nullable()
                  ->comment('Tanggal awal minggu tersebut (opsional)');

            // ── Progress ──────────────────────────────────────────────────
            // File 5: "42,50%" → perlu sanitize koma → titik saat parsing
            $table->decimal('rencana_pct', 6, 2)->nullable()
                  ->comment('Progress rencana kumulatif (%)');
            $table->decimal('realisasi_pct', 6, 2)->nullable()
                  ->comment('Progress realisasi kumulatif (%)');
            $table->decimal('deviasi_pct', 7, 2)->nullable()
                  ->comment('realisasi_pct - rencana_pct (negatif = terlambat)');

            // ── Keterangan ────────────────────────────────────────────────
            // File 5: "Critical", "Material Delay"
            $table->string('keterangan', 100)->nullable()
                  ->comment('Catatan kondisi minggu tersebut');

            $table->timestamps();

            // Satu proyek hanya boleh punya satu data per minggu
            $table->unique(['project_id', 'week_number'], 'uq_project_week');

            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_progress_curves');
    }
};