<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tambah deskripsi di kategori (boleh kosong)
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
        });

        // Tambah field di transaksi
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'code')) {
                $table->string('code')->unique()->after('id');
            }

            if (! Schema::hasColumn('transactions', 'title')) {
                $table->string('title')->after('date'); // nama transaksi
            }

            if (! Schema::hasColumn('transactions', 'type')) {
                // income / expense
                $table->string('type', 20)->default('expense')->after('title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'description')) {
                $table->dropColumn('description');
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'code')) {
                $table->dropUnique(['code']);
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('transactions', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('transactions', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
