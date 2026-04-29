<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_financial_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->unique()
                ->constrained('projects')
                ->cascadeOnDelete();

            $table->decimal('penjualan', 20, 2)->nullable();

            $table->decimal('material', 20, 2)->nullable();
            $table->decimal('upah', 20, 2)->nullable();
            $table->decimal('alat', 20, 2)->nullable();
            $table->decimal('subkon', 20, 2)->nullable();

            $table->decimal('fasilitas', 20, 2)->nullable();
            $table->decimal('sekretariat', 20, 2)->nullable();
            $table->decimal('kendaraan', 20, 2)->nullable();
            $table->decimal('personalia', 20, 2)->nullable();
            $table->decimal('keuangan', 20, 2)->nullable();
            $table->decimal('umum', 20, 2)->nullable();

            $table->decimal('biaya_pemeliharaan', 20, 2)->nullable();
            $table->decimal('risiko', 20, 2)->nullable();

            $table->decimal('beban_pph_final', 20, 2)->nullable();
            $table->decimal('laba_kotor', 20, 2)->nullable();
            $table->decimal('lsp', 20, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_financial_summary');
    }
};
