<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- projects: drop composite-unique index (if present) ---
        try {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropUnique('projects_project_code_user_id_unique');
            });
        } catch (\Throwable $e) {
            // Index doesn't exist — skip
        }

        // --- projects: drop FK on user_id (if present) ---
        if (Schema::hasColumn('projects', 'user_id')) {
            try {
                Schema::table('projects', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                });
            } catch (\Throwable $e) {
                // FK missing — skip
            }

            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }

        // Dedupe duplicate project_code rows: keep highest id, delete the rest
        $duplicates = DB::table('projects')
            ->select('project_code', DB::raw('MAX(id) AS keeper'))
            ->groupBy('project_code')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('projects')
                ->where('project_code', $dup->project_code)
                ->where('id', '!=', $dup->keeper)
                ->delete();
        }

        // Restore single-column unique on project_code (no-op if present)
        try {
            Schema::table('projects', function (Blueprint $table) {
                $table->unique('project_code');
            });
        } catch (\Throwable $e) {
            // Already unique
        }

        // --- ingestion_files: drop FK + user_id column ---
        if (Schema::hasColumn('ingestion_files', 'user_id')) {
            try {
                Schema::table('ingestion_files', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                });
            } catch (\Throwable $e) {
            }

            Schema::table('ingestion_files', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }

    public function down(): void
    {
        try {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropUnique(['project_code']);
            });
        } catch (\Throwable $e) {
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('ingestion_file_id')
                ->constrained('users')->nullOnDelete();
            $table->unique(['project_code', 'user_id']);
        });

        Schema::table('ingestion_files', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')
                ->constrained('users')->nullOnDelete();
        });
    }
};
