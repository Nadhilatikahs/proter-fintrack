<?php

namespace App\Filament\Widgets;

use App\Models\Budget;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class BudgetOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null; // tidak auto refresh

    protected function getStats(): array
    {
        $userId = Auth::id();
        $now    = Carbon::now();

        $month = $now->month;
        $year  = $now->year;

        $totalBudget = Budget::where('user_id', $userId)
            ->where('month', $month)
            ->where('year', $year)
            ->sum('limit_amount');

        $totalExpense = Transaction::where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereHas('category', fn ($q) =>
                $q->where('type', 'expense')
            )
            ->sum('amount');

        $remaining = $totalBudget - $totalExpense;

        $usage = $totalBudget > 0
            ? round(($totalExpense / $totalBudget) * 100, 2)
            : 0;

        return [
            Stat::make('Budget bulan ini', 'Rp ' . number_format($totalBudget, 0, ',', '.'))
                ->description('Total batas pengeluaran bulan ini')
                ->color('primary'),

            Stat::make('Pengeluaran vs Budget', 'Rp ' . number_format($totalExpense, 0, ',', '.'))
                ->description($usage . '% dari budget')
                ->color(match (true) {
                    $usage >= 100 => 'danger',
                    $usage >= 80  => 'warning',
                    default       => 'success',
                }),

            Stat::make('Sisa budget', 'Rp ' . number_format($remaining, 0, ',', '.'))
                ->description($remaining < 0 ? 'Sudah melebihi batas' : 'Masih dalam batas aman')
                ->color($remaining < 0 ? 'danger' : 'success'),
        ];
    }
}
