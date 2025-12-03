<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budget_goals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');         // nama budget/goal
            $table->text('description')->nullable();

            // budget atau goal
            $table->string('type', 20);     // 'budget' | 'goal'

            $table->decimal('target_amount', 15, 2);
            $table->date('target_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_goals');
    }
};
