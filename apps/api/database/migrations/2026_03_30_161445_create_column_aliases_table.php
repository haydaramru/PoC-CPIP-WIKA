<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * column_aliases — mapping nama kolom Excel → field standar sistem
     *
     * DB-backed alias management untuk menggantikan hardcoded const ALIASES
     * di ProjectImport.php. Admin bisa tambah alias baru via UI tanpa deploy ulang.
     *
     * Seed awal: semua alias yang sudah ada di const ALIASES dipindah ke sini
     * melalui DatabaseSeeder saat pertama kali migrate.
     *
     * Context membedakan alias yang berlaku untuk ingestion level mana:
     *   project    → kolom di tabel projects (File 1, 2, 3)
     *   work_item  → kolom di tabel project_work_items (rekap HPP)
     *   material   → kolom di tabel project_material_logs
     *   equipment  → kolom di tabel project_equipment_logs
     *   period     → kolom di tabel project_periods (header metadata)
     *   s_curve    → kolom di tabel project_progress_curves
     *
     * Relasi:
     *   Standalone — nullable FK ke users (untuk audit siapa yang tambah)
     */
    public function up(): void
    {
        Schema::create('column_aliases', function (Blueprint $table) {
            $table->id();

            // ── Alias dari Excel ──────────────────────────────────────────
            // Nilai setelah normalizeHeader(): lowercase, spasi→_, non-word dihapus
            // Contoh: "Rencana Biaya (M)" → "rencana_biaya_m"
            $table->string('alias', 120)
                  ->comment('Header kolom Excel setelah normalisasi');

            // ── Target field standar ──────────────────────────────────────
            // Nama kolom di database, e.g. "planned_cost", "item_name"
            $table->string('target_field', 80)
                  ->comment('Nama field standar di sistem');

            // ── Context ───────────────────────────────────────────────────
            // Membatasi alias hanya berlaku untuk ingestion level tertentu
            // NULL = berlaku untuk semua context
            $table->string('context', 30)->nullable()
                  ->comment('project | work_item | material | equipment | period | s_curve | null=all');

            // ── Status ────────────────────────────────────────────────────
            // Soft disable: alias yang salah dinonaktifkan, tidak dihapus
            // agar history ingestion lama tidak terpengaruh
            $table->boolean('is_active')->default(true)
                  ->comment('False = alias dinonaktifkan (soft delete)');

            // ── Audit ─────────────────────────────────────────────────────
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->comment('User yang menambahkan alias ini (null = seeder/sistem)');

            $table->timestamps();

            // Kombinasi alias + context harus unik
            // (alias "planned" bisa map ke planned_cost untuk context "project"
            //  sekaligus ke hpp_plan untuk context "period" — tapi tidak boleh duplikat)
            $table->unique(['alias', 'context'], 'uq_alias_context');

            $table->index('is_active');
            $table->index('target_field');
            $table->index('context');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('column_aliases');
    }
};