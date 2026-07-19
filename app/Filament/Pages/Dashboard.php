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
        $from  = $this->fromDate ? Carbon::parse($this->fromDate)->startOfDay() : $today->copy()->startOfMonth();
        $to    = $this->toDate   ? Carbon::parse($this->toDate)->endOfDay() : $today->copy()->endOfMonth();

        if (! $userId) {
            return [
                'userName'         => 'User',
                'summary'          => ['income' => 0, 'expense' => 0, 'balance' => 0],
                'categoryChart'    => ['labels' => [], 'data' => []],
                'dailyChart'       => ['labels' => [], 'income' => [], 'expense' => []],
                'lastTransactions' => collect(),
                'fromDate'         => $from->toDateString(),
                'toDate'           => $to->toDateString(),
            ];
        }

        // summary - fix: ensure we're getting all transactions for current month
        $income = (float) Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount') ?: 0;

        $expense = (float) Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount') ?: 0;

        // pie by category (expense)
        $categoryRows = Transaction::query()
            ->selectRaw('COALESCE(categories.name, "Uncategorised") AS label, SUM(transactions.amount) AS total')
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.date', [$from->toDateString(), $to->toDateString()])
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
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Weekly data by category (for line chart: Saving, Lifestyle, Food)
        $weeklyCategoryData = Transaction::query()
            ->selectRaw('WEEK(date, 1) as week_number,
                categories.name as category_name,
                SUM(transactions.amount) as total')
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('week_number', 'category_name')
            ->orderBy('week_number')
            ->get();

        // Group by week and category
        $weeks = [];
        $savingData = [];
        $lifestyleData = [];
        $foodData = [];
        
        $weekNumbers = $weeklyCategoryData->pluck('week_number')->unique()->sort()->values();
        foreach ($weekNumbers as $weekNum) {
            $weeks[] = "week " . ($weekNum - $weekNumbers->first() + 1);
            $weekData = $weeklyCategoryData->where('week_number', $weekNum);
            
            $savingData[] = $weekData->filter(fn($d) => 
                stripos($d->category_name ?? '', 'saving') !== false || 
                stripos($d->category_name ?? '', 'save') !== false
            )->sum('total');
            
            $lifestyleData[] = $weekData->filter(fn($d) => 
                stripos($d->category_name ?? '', 'lifestyle') !== false || 
                stripos($d->category_name ?? '', 'life') !== false
            )->sum('total');
            
            $foodData[] = $weekData->filter(fn($d) => 
                stripos($d->category_name ?? '', 'food') !== false || 
                stripos($d->category_name ?? '', 'makan') !== false
            )->sum('total');
        }
        
        // If no weeks, create default 4 weeks
        if (empty($weeks)) {
            $weeks = ['week 1', 'week 2', 'week 3', 'week 4'];
            $savingData = [0, 0, 0, 0];
            $lifestyleData = [0, 0, 0, 0];
            $foodData = [0, 0, 0, 0];
        }

        $lastTransactions = Transaction::with('category')
            ->where('user_id', $userId)
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->limit(10)
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

            'weeklyChart' => [
                'weeks' => $weeks,
                'saving' => $savingData,
                'lifestyle' => $lifestyleData,
                'food' => $foodData,
            ],

            'lastTransactions' => $lastTransactions,
            'fromDate'         => $from->toDateString(),
            'toDate'           => $to->toDateString(),
        ];
    }
}
