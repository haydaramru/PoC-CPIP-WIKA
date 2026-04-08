<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harsat_histories', function (Blueprint $table) {
            $table->id();
            $table->string('category', 100);   // e.g. Besi, Beton, Jembatan
            $table->string('category_key', 50); // slug e.g. besi, beton, jembatan
            $table->year('year');
            $table->decimal('value', 12, 2);   // harga satuan dalam jutaan
            $table->string('unit', 50)->nullable(); // e.g. kg, m3, m
            $table->timestamps();

            $table->unique(['category_key', 'year']);
            $table->index(['category_key', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harsat_histories');
    }
};
