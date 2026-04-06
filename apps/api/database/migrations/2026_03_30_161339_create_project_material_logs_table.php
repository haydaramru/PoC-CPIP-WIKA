<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * project_material_logs — log material strategis (L5A)
     *
     * Detail tagihan material per supplier per periode.
     * Terikat ke project_periods, dengan work_item_id opsional
     * (karena File 4/5 tidak selalu menyebut item pekerjaan spesifik
     * untuk tiap baris material).
     *
     * Dari File 4:  no | deskripsi_vendor | material | qty | unit | harga_satuan | total_tagihan
     * Dari File 5 (LOG_MATERIAL_STRATEGIS): No | Supplier | Jenis Material | Qty | Satuan | Harga Satuan | Total Tagihan
     *
     * Relasi:
     *   project_periods   (1) ──── (many) project_material_logs
     *   project_work_items (1) ──── (many) project_material_logs [optional]
     */
    public function up(): void
    {
        Schema::create('project_material_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('period_id')
                  ->constrained('project_periods')
                  ->cascadeOnDelete();

            // Opsional — kalau file memuat info item pekerjaan terkait
            $table->foreignId('work_item_id')
                  ->nullable()
                  ->constrained('project_work_items')
                  ->nullOnDelete();

            // ── Supplier ──────────────────────────────────────────────────
            // File 4: "pt sinar beton surya" | File 5: "PT Sinar Beton"
            $table->string('supplier_name', 200)
                  ->comment('Nama supplier/vendor material');

            // ── Material ──────────────────────────────────────────────────
            // File 4: "beton k-350" | File 5: "Beton K-350", "Besi Ulir D16"
            $table->string('material_type', 200)
                  ->comment('Jenis/nama material');

            // ── Quantity & satuan ─────────────────────────────────────────
            $table->decimal('qty', 15, 4)->nullable()
                  ->comment('Jumlah material');
            $table->string('satuan', 30)->nullable()
                  ->comment('Satuan, e.g. m3, kg, ton, unit');

            // ── Harga ─────────────────────────────────────────────────────
            $table->decimal('harga_satuan', 20, 2)->nullable()
                  ->comment('Harga per satuan (IDR)');
            $table->decimal('total_tagihan', 20, 2)->nullable()
                  ->comment('Total tagihan = qty * harga_satuan (bisa negatif = diskon/potongan)');

            // ── Flag khusus ───────────────────────────────────────────────
            // File 5 punya baris "DISCOUNT / Potongan Vendor" dengan total negatif
            $table->boolean('is_discount')->default(false)
                  ->comment('True jika baris ini adalah potongan/diskon vendor');

            // ── Raw data ──────────────────────────────────────────────────
            // Simpan row_number dari Excel untuk debugging ingestion
            $table->unsignedSmallInteger('source_row')->nullable()
                  ->comment('Nomor baris di file Excel sumber');

            $table->timestamps();

            $table->index('period_id');
            $table->index('supplier_name');
            $table->index('material_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_material_logs');
    }
};