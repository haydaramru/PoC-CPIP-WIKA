<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * project_equipment_logs — log penggunaan alat berat (L5B)
     *
     * Detail pemakaian alat berat per vendor per periode.
     * Terikat ke project_periods, dengan work_item_id opsional.
     *
     * Dari File 5 (PENGGUNAAN_ALAT_BERAT):
     *   Vendor | Alat | Jam Kerja | Rate/Jam | Total Biaya | Status
     *
     * Catatan: vendor bisa None (baris lanjutan dari vendor di atas),
     * parser harus forward-fill nama vendor.
     *
     * Relasi:
     *   project_periods    (1) ──── (many) project_equipment_logs
     *   project_work_items (1) ──── (many) project_equipment_logs [optional]
     */
    public function up(): void
    {
        Schema::create('project_equipment_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('period_id')
                  ->constrained('project_periods')
                  ->cascadeOnDelete();

            $table->foreignId('work_item_id')
                  ->nullable()
                  ->constrained('project_work_items')
                  ->nullOnDelete();

            // ── Vendor ────────────────────────────────────────────────────
            // File 5: "PT Alat Jaya", "CV Mandiri"
            // Baris ke-2 PT Alat Jaya tidak repeat nama vendor — forward-fill saat parsing
            $table->string('vendor_name', 200)
                  ->comment('Nama vendor/penyedia alat berat');

            // ── Alat ──────────────────────────────────────────────────────
            // File 5: "Excavator PC200", "Mobile Crane", "Dump Truck"
            $table->string('equipment_name', 200)
                  ->comment('Nama dan tipe alat berat');

            // ── Pemakaian & biaya ─────────────────────────────────────────
            // File 5: Jam Kerja | Rate/Jam | Total Biaya
            $table->decimal('jam_kerja', 10, 2)->nullable()
                  ->comment('Total jam pemakaian alat');
            $table->decimal('rate_per_jam', 20, 2)->nullable()
                  ->comment('Harga sewa per jam (IDR)');
            $table->decimal('total_biaya', 20, 2)->nullable()
                  ->comment('jam_kerja * rate_per_jam (IDR)');

            // ── Status pembayaran ─────────────────────────────────────────
            // File 5: "Paid", "Pending"
            $table->string('payment_status', 30)->nullable()
                  ->comment('Status: Paid | Pending | Partial');

            $table->unsignedSmallInteger('source_row')->nullable()
                  ->comment('Nomor baris di file Excel sumber');

            $table->timestamps();

            $table->index('period_id');
            $table->index('vendor_name');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_equipment_logs');
    }
};