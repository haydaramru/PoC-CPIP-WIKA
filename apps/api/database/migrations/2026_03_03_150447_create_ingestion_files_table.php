<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingestion_files', function (Blueprint $table) {
            $table->id();
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('disk')->default('local');
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'partial'])
                  ->default('pending');
            $table->integer('total_rows')->default(0);
            $table->integer('imported_rows')->default(0);
            $table->integer('skipped_rows')->default(0);
            $table->json('errors')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingestion_files');
    }
};