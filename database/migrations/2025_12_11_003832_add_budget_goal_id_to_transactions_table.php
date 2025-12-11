<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Tambah kolom budget_goal_id kalau belum ada
            if (! Schema::hasColumn('transactions', 'budget_goal_id')) {
                $table->foreignId('budget_goal_id')
                    ->nullable()
                    ->after('category_id')
                    ->constrained('budget_goals')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'budget_goal_id')) {
                // Drop FK lalu kolomnya
                $table->dropForeign(['budget_goal_id']);
                $table->dropColumn('budget_goal_id');
            }
        });
    }
};
