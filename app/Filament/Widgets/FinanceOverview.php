<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinanceOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth   = Carbon::now()->endOfMonth();

        $income = Transaction::query()
            ->whereHas('category', fn ($q) => $q->where('type', 'income'))
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $expense = Transaction::query()
            ->whereHas('category', fn ($q) => $q->where('type', 'expense'))
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $balance = $income - $expense;

        return [
            Stat::make('Pemasukan bulan ini', 'Rp ' . number_format($income, 0, ',', '.'))
                ->description('Total kategori income')
                ->color('success'),

            Stat::make('Pengeluaran bulan ini', 'Rp ' . number_format($expense, 0, ',', '.'))
                ->description('Total kategori expense')
                ->color('danger'),

            Stat::make('Saldo bulan ini', 'Rp ' . number_format($balance, 0, ',', '.'))
                ->description($balance >= 0 ? 'Surplus' : 'Defisit')
                ->color($balance >= 0 ? 'success' : 'danger'),
        ];
    }
}
