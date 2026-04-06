<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * project_work_items — breakdown HPP per item pekerjaan (L4)
     *
     * Hierarkis (self-referencing):
     *   Level 0 = Kategori    → "Pekerjaan Struktur", "Pekerjaan Arsitektur"
     *   Level 1 = Sub-item    → "Pengadaan Besi Beton", "Beton ready mix k-350"
     *   Level 2 = Detail item → (opsional, untuk file yang lebih granular)
     *
     * Terikat ke project_periods bukan ke projects langsung,
     * karena realisasi berubah setiap bulan (snapshot per periode).
     *
     * Relasi:
     *   project_periods (1) ──── (many) project_work_items
     *   project_work_items (1) ──── (many) project_work_items [self-ref]
     */
    public function up(): void
    {
        Schema::create('project_work_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('period_id')
                  ->constrained('project_periods')
                  ->cascadeOnDelete();

            // ── Hierarki self-referencing ─────────────────────────────────
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('project_work_items')
                  ->nullOnDelete()
                  ->comment('NULL = item level atas (kategori)');

            $table->unsignedTinyInteger('level')->default(0)
                  ->comment('0 = kategori, 1 = sub-item, 2 = detail');

            // ── Nomor & nama item ─────────────────────────────────────────
            // Dari File 4: (1.0, 'Pekerjaan Struktur') → (None, 'Pengadaan Besi Beton')
            // Dari File 5: ('I.', 'PEKERJAAN PERSIAPAN') → (1.1, 'Mobilisasi Alat')
            $table->string('item_no', 20)->nullable()
                  ->comment('Nomor item, e.g. "I.", "1.1", "2.2"');
            $table->string('item_name', 255)
                  ->comment('Nama item pekerjaan');
            $table->unsignedSmallInteger('sort_order')->default(0)
                  ->comment('Urutan tampil dalam satu parent');

            // ── Biaya ─────────────────────────────────────────────────────
            // Dari File 4: Budget Awal | Addendum | Total Budget | Realisasi | Deviasi | %
            $table->decimal('budget_awal', 20, 2)->nullable()
                  ->comment('Budget awal sebelum addendum (IDR)');
            $table->decimal('addendum', 20, 2)->nullable()->default(0)
                  ->comment('Nilai addendum, bisa negatif');
            $table->decimal('total_budget', 20, 2)->nullable()
                  ->comment('budget_awal + addendum');
            $table->decimal('realisasi', 20, 2)->nullable()
                  ->comment('Realisasi biaya ITD (inception to date)');
            $table->decimal('deviasi', 20, 2)->nullable()
                  ->comment('total_budget - realisasi (positif = under, negatif = over)');
            $table->decimal('deviasi_pct', 8, 4)->nullable()
                  ->comment('deviasi / total_budget * 100');

            // ── Flag baris total ──────────────────────────────────────────
            // Dari File 5: baris "TOTAL COST ITD" bukan item pekerjaan, tapi summary
            $table->boolean('is_total_row')->default(false)
                  ->comment('True jika ini baris total/subtotal, bukan item pekerjaan');

            $table->timestamps();

            // Index untuk query hierarki
            $table->index(['period_id', 'parent_id']);
            $table->index(['period_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_work_items');
    }
};