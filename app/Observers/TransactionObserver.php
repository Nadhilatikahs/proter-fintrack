<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\BudgetGoal;

class TransactionObserver
{
    /**
     * Handle after transaction created
     */
    public function created(Transaction $transaction): void
    {
        if ($transaction->type !== 'income') {
            return;
        }

        // Ambil goal user (asumsi 1 goal utama)
        $goal = BudgetGoal::where('user_id', $transaction->user_id)
            ->where('type', 'goal')
            ->first();

        if (! $goal) {
            return;
        }

        // Tambahkan income ke current
        $goal->increment('current', $transaction->amount);
    }

    /**
     * Handle delete (rollback)
     */
    public function deleted(Transaction $transaction): void
    {
        if ($transaction->type !== 'income') {
            return;
        }

        $goal = BudgetGoal::where('user_id', $transaction->user_id)
            ->where('type', 'goal')
            ->first();

        if (! $goal) {
            return;
        }

        $goal->decrement('current', $transaction->amount);
    }
    public function updated(Transaction $transaction): void
    {
        if ($transaction->type !== 'income') {
            return;
        }

        if ($transaction->wasChanged('amount')) {
            $diff = $transaction->amount - $transaction->getOriginal('amount');

            $goal = BudgetGoal::where('user_id', $transaction->user_id)
                ->where('type', 'goal')
                ->first();

            if ($goal) {
                $goal->increment('current', $diff);
            }
        }
    }

}
