<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\BudgetGoal;

class MigrateTransactionsToBudgets extends Command
{
    protected $signature = 'budget:migrate-transactions {--dry-run}';
    protected $description = 'Migrasi transaksi lama ke budget berdasarkan category';

    public function handle(): int
    {
        $this->info('🔄 Mulai migrasi transaksi ke budget...');

        $dryRun = $this->option('dry-run');

        // Ambil semua budget (type = budget) yang punya category_id
        $budgets = BudgetGoal::where('type', 'budget')
            ->whereNotNull('category_id')
            ->get()
            ->keyBy('category_id');

        if ($budgets->isEmpty()) {
            $this->error('❌ Tidak ada budget dengan category_id');
            return Command::FAILURE;
        }

        // Ambil transaksi expense yang belum punya budget_goal_id
        $transactions = Transaction::where('type', 'expense')
            ->whereNull('budget_goal_id')
            ->whereNotNull('category_id')
            ->get();

        if ($transactions->isEmpty()) {
            $this->info('✅ Tidak ada transaksi yang perlu dimigrasi.');
            return Command::SUCCESS;
        }

        $updated = 0;

        foreach ($transactions as $trx) {
            if (! $budgets->has($trx->category_id)) {
                continue; // tidak ada budget yg cocok
            }

            $budget = $budgets[$trx->category_id];

            if ($dryRun) {
                $this->line(
                    "[DRY] TRX {$trx->id} → Budget {$budget->name}"
                );
            } else {
                $trx->update([
                    'budget_goal_id' => $budget->id,
                ]);
                $updated++;
            }
        }

        if ($dryRun) {
            $this->warn("⚠️ DRY RUN selesai. Tidak ada data diubah.");
        } else {
            $this->info("✅ Migrasi selesai. {$updated} transaksi terhubung ke budget.");
        }

        return Command::SUCCESS;
    }
}
