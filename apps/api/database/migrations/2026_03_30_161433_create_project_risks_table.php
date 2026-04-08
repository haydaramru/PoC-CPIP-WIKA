<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * project_risks — risk register finansial (L7)
     *
     * Input manual oleh Project Manager atau Data Admin.
     * Tidak di-ingest dari Excel — ini adalah data yang diisi
     * langsung di aplikasi CPIP.
     *
     * Tujuan: investigasi potensi kerugian finansial per proyek,
     * lengkap dengan estimasi impact IDR dan status mitigasi.
     *
     * Relasi:
     *   projects (1) ──── (many) project_risks
     */
    public function up(): void
    {
        Schema::create('project_risks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->cascadeOnDelete();

            // ── Identifikasi risiko ───────────────────────────────────────
            $table->string('risk_code', 20)->nullable()
                  ->comment('Kode risiko, e.g. RSK-001');
            $table->string('risk_title', 255)
                  ->comment('Judul singkat risiko, e.g. "Material delay akibat cuaca"');
            $table->text('risk_description')->nullable()
                  ->comment('Deskripsi lengkap risiko');

            // ── Kategori ──────────────────────────────────────────────────
            // Kategori umum risiko proyek konstruksi
            $table->string('category', 50)->nullable()
                  ->comment('cost | schedule | quality | safety | scope | external');

            // ── Dampak finansial ──────────────────────────────────────────
            $table->decimal('financial_impact_idr', 20, 2)->nullable()
                  ->comment('Estimasi dampak finansial dalam IDR');

            // ── Probabilitas & severity ───────────────────────────────────
            // Skala 1-5 untuk probability dan impact (standard risk matrix)
            $table->unsignedTinyInteger('probability')->nullable()
                  ->comment('1=sangat rendah, 2=rendah, 3=sedang, 4=tinggi, 5=sangat tinggi');
            $table->unsignedTinyInteger('impact')->nullable()
                  ->comment('1=tidak signifikan, 2=minor, 3=moderat, 4=major, 5=kritis');
            $table->string('severity', 20)->nullable()
                  ->comment('low | medium | high | critical — dihitung dari probability × impact');

            // ── Mitigasi ──────────────────────────────────────────────────
            $table->text('mitigation')->nullable()
                  ->comment('Rencana atau tindakan mitigasi yang sudah/akan dilakukan');

            // ── Status ────────────────────────────────────────────────────
            $table->string('status', 20)->default('open')
                  ->comment('open | mitigated | closed | monitoring');

            // ── Penanggung jawab & tanggal ────────────────────────────────
            $table->string('owner', 100)->nullable()
                  ->comment('Nama PIC yang bertanggung jawab atas risiko ini');
            $table->date('identified_at')->nullable()
                  ->comment('Tanggal risiko diidentifikasi');
            $table->date('target_resolved_at')->nullable()
                  ->comment('Target tanggal risiko di-resolve');

            $table->timestamps();

            $table->index('project_id');
            $table->index('status');
            $table->index('severity');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_risks');
    }
};