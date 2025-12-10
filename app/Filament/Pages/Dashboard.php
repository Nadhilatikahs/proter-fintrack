<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';
    protected static string $view   = 'filament.pages.dashboard';

    public ?string $fromDate = null;
    public ?string $toDate   = null;

    public function updatedFromDate(): void
    {
        // re-render aja, data diambil di getViewData()
    }

    public function updatedToDate(): void
    {
        // sama
    }

    protected function getViewData(): array
    {
        $user   = Auth::user();
        $userId = $user?->id;

        $today = Carbon::today();
        $from  = $this->fromDate ?: $today->copy()->startOfMonth()->toDateString();
        $to    = $this->toDate   ?: $today->copy()->endOfMonth()->toDateString();

        if (! $userId) {
            return [
                'userName'         => 'User',
                'summary'          => ['income' => 0, 'expense' => 0, 'balance' => 0],
                'categoryChart'    => ['labels' => [], 'data' => []],
                'dailyChart'       => ['labels' => [], 'income' => [], 'expense' => []],
                'lastTransactions' => collect(),
                'fromDate'         => $from,
                'toDate'           => $to,
            ];
        }

        // summary
        $income = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$from, $to])
            ->sum('amount');

        $expense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$from, $to])
            ->sum('amount');

        // pie by category (expense)
        $categoryRows = Transaction::query()
            ->selectRaw('COALESCE(categories.name, "Uncategorised") AS label, SUM(transactions.amount) AS total')
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.date', [$from, $to])
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // bar cashflow per day
        $dailyRows = Transaction::query()
            ->selectRaw('date,
                SUM(CASE WHEN type = "income"  THEN amount ELSE 0 END) AS income,
                SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) AS expense')
            ->where('user_id', $userId)
            ->whereBetween('date', [$from, $to])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $lastTransactions = Transaction::with('category')
            ->where('user_id', $userId)
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return [
            'userName' => $user->name ?? 'User',

            'summary' => [
                'income'  => $income,
                'expense' => $expense,
                'balance' => $income - $expense,
            ],

            'categoryChart' => [
                'labels' => $categoryRows->pluck('label')->values(),
                'data'   => $categoryRows->pluck('total')->values(),
            ],

            'dailyChart' => [
                'labels'  => $dailyRows->pluck('date')->values(),
                'income'  => $dailyRows->pluck('income')->values(),
                'expense' => $dailyRows->pluck('expense')->values(),
            ],

            'lastTransactions' => $lastTransactions,
            'fromDate'         => $from,
            'toDate'           => $to,
        ];
    }
}
