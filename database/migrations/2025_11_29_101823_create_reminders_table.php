<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('type'); // 'budget_over_limit', 'budget_warning', 'goal_due_soon', etc.

            // referensi ke budget/goal jika perlu
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('related_model')->nullable(); // App\Models\Budget / Goal

            $table->text('title')->nullable();   // judul singkat
            $table->text('message');             // pesan dari AI
            $table->json('data')->nullable();    // context mentah (amount, tanggal, dll.)

            $table->timestamp('sent_at')->nullable(); // kapan dikirim email
            $table->boolean('is_read')->default(false);

            $table->timestamps();

            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
