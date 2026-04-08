<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Klasifikasi proyek
            $table->string('sbu', 100)->nullable()->after('division');               // Gedung RS | Jembatan | Sanitasi | Bandara
            $table->string('contract_type', 100)->nullable()->after('owner');        // Lumpsum | Design & Build | Tender | Penunjukan Langsung
            $table->string('payment_method', 100)->nullable()->after('contract_type'); // Termin Progress | Lumpsum | Milestone
            $table->string('partnership', 50)->nullable()->after('payment_method');  // JO | Non JO
            $table->string('funding_source', 100)->nullable()->after('partnership'); // APBN | APBD | Swasta
            $table->string('location', 255)->nullable()->after('funding_source');    // Surabaya, Jawa Timur | Jakarta | dll

            // Profitabilitas
            $table->decimal('gross_profit_pct', 6, 2)->nullable()->after('progress_pct'); // margin gross profit dalam %
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'sbu',
                'contract_type',
                'payment_method',
                'partnership',
                'funding_source',
                'location',
                'gross_profit_pct',
            ]);
        });
    }
};
