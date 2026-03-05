<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('ingestion_file_id')
                  ->nullable()
                  ->constrained('ingestion_files')
                  ->nullOnDelete()
                  ->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\IngestionFile::class);
            $table->dropColumn('ingestion_file_id');
        });
    }
};