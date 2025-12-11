<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_goals', function (Blueprint $table) {
            // Simpan progress terakhir yang sudah dikirim notif
            $table->unsignedTinyInteger('last_notified_progress')
                ->nullable()
                ->after('target_amount');
        });
    }

    public function down(): void
    {
        Schema::table('budget_goals', function (Blueprint $table) {
            if (Schema::hasColumn('budget_goals', 'last_notified_progress')) {
                $table->dropColumn('last_notified_progress');
            }
        });
    }
};
