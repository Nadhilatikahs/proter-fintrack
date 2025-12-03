<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('budget_goals', function (Blueprint $table) {
            if (! Schema::hasColumn('budget_goals', 'period_type')) {
                // daily, weekly, biweekly, monthly, yearly
                $table->string('period_type', 20)
                    ->nullable()
                    ->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('budget_goals', function (Blueprint $table) {
            if (Schema::hasColumn('budget_goals', 'period_type')) {
                $table->dropColumn('period_type');
            }
        });
    }
};
