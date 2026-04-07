<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_material_logs', function (Blueprint $table) {
            $table->string('tahun_perolehan', 4)->nullable()->after('supplier_name');
            $table->string('lokasi_vendor', 255)->nullable()->after('tahun_perolehan');
            $table->string('rating_performa', 10)->nullable()->after('lokasi_vendor');   // e.g. "4/5"
            $table->string('realisasi_pengiriman', 100)->nullable()->after('rating_performa'); // e.g. "100% (Selesai)"
            $table->string('deviasi_harga_market', 50)->nullable()->after('realisasi_pengiriman'); // e.g. "+2%"
            $table->text('catatan_monitoring')->nullable()->after('deviasi_harga_market');
        });
    }

    public function down(): void
    {
        Schema::table('project_material_logs', function (Blueprint $table) {
            $table->dropColumn([
                'tahun_perolehan',
                'lokasi_vendor',
                'rating_performa',
                'realisasi_pengiriman',
                'deviasi_harga_market',
                'catatan_monitoring',
            ]);
        });
    }
};
