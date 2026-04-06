<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->index('division');
            $table->index('status');
            $table->index('project_year');
            $table->index('cpi');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['division']);
            $table->dropIndex(['status']);
            $table->dropIndex(['project_year']);
            $table->dropIndex(['cpi']);
        });
    }
};
