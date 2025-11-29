<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reminder_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            // kalau pengeluaran sudah melewati X% dari budget
            $table->unsignedTinyInteger('budget_warning_threshold')
                  ->default(80); // 80%

            // kirim reminder kalau GOAL tinggal N hari lagi
            $table->unsignedSmallInteger('goal_days_before_due')
                  ->default(7); // 7 hari

            // jam harian untuk kirim reminder (0-23)
            $table->unsignedTinyInteger('daily_digest_hour')
                  ->default(20); // jam 20.00

            // channel
            $table->boolean('notify_email')->default(true);
            $table->boolean('notify_in_app')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_settings');
    }
};
