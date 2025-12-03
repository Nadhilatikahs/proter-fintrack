<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\Auth;

class MonthlyFinanceOverview extends BaseWidget
{
    protected static ?int $sort = 1; // urutan di dashboard

    protected function getCards(): array
    {
        $userId = Auth::id();
        $now    = Carbon::now();

        $start = $now->copy()->startOfMonth();
        $end   = $now->copy()->endOfMonth();

        $income = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        $expense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        $balance = $income - $expense;

        $incomeLabel  = 'Rp ' . number_format($income, 0, ',', '.');
        $expenseLabel = 'Rp ' . number_format($expense, 0, ',', '.');
        $balanceLabel = 'Rp ' . number_format($balance, 0, ',', '.');

        return [
            Card::make('Pemasukan bulan ini', $incomeLabel)
                ->description('Total income ' . $now->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-o-arrow-up-circle')
                ->color('success'),

            Card::make('Pengeluaran bulan ini', $expenseLabel)
                ->description('Total expense ' . $now->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-o-arrow-down-circle')
                ->color('danger'),

            Card::make('Saldo bulan ini', $balanceLabel)
                ->description('Income - Expense bulan ini')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color($balance >= 0 ? 'primary' : 'warning'),
        ];
    }
}
