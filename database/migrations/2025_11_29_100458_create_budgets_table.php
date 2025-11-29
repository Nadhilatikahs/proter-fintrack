<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Budget bisa per kategori, opsional
            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->unsignedTinyInteger('month'); // 1-12
            $table->unsignedSmallInteger('year');
            $table->decimal('limit_amount', 15, 2);

            $table->timestamps();

            $table->unique(['user_id', 'category_id', 'month', 'year'], 'user_cat_month_year_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
