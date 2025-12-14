<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Transaction;
use App\Models\BudgetGoal;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class Reports extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Reports';
    protected static ?string $navigationGroup = 'MENU';
    protected static ?int    $navigationSort  = 40;

    protected static string $view = 'filament.pages.reports';

    public function getViewData(): array
    {
        $userId = auth()->id();

        /*
        |--------------------------------------------------------------------------
        | DATE (GLOBAL – WAJIB ADA)
        |--------------------------------------------------------------------------
        */
        $date = request('date')
            ? Carbon::parse(request('date'))->startOfDay()
            : now()->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | RANGE FILTER (CASH FLOW)
        |--------------------------------------------------------------------------
        */
        $from = request('from')
            ? Carbon::parse(request('from'))->startOfDay()
            : now()->startOfMonth();

        $to = request('to')
            ? Carbon::parse(request('to'))->endOfDay()
            : now()->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | CASH FLOW
        |--------------------------------------------------------------------------
        */
        $cashflowRaw = Transaction::select(
                DB::raw('DATE(date) as day'),
                DB::raw("SUM(CASE WHEN type='income' THEN amount ELSE 0 END) as income"),
                DB::raw("SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) as expense")
            )
            ->where('user_id', $userId)
            ->whereBetween('date', [$from, $to])
            ->groupBy(DB::raw('DATE(date)'))
            ->orderBy('day')
            ->get();

        $period = Carbon::parse($from)->daysUntil($to);

        $labels = [];
        $income = [];
        $expense = [];

        foreach ($period as $d) {
            $row = $cashflowRaw->firstWhere('day', $d->format('Y-m-d'));

            $labels[]  = $d->format('d M');
            $income[]  = (int) ($row->income ?? 0);
            $expense[] = (int) ($row->expense ?? 0);
        }

        $totalIncome  = array_sum($income);
        $totalExpense = array_sum($expense);
        $selisih      = $totalIncome - $totalExpense;

        /*
        |--------------------------------------------------------------------------
        | BUDGET
        |--------------------------------------------------------------------------
        */
        $budgets = BudgetGoal::where('user_id', $userId)
            ->where('type', 'budget')
            ->withSum(
                ['transactions as spent' => fn ($q) => $q->where('type', 'expense')],
                'amount'
            )
            ->get()
            ->map(function ($b) {
                $b->amount  = (int) $b->target_amount;
                $b->spent   = (int) ($b->spent ?? 0);
                $b->remain  = max($b->amount - $b->spent, 0);
                $b->percent = $b->amount > 0
                    ? min(100, round(($b->spent / $b->amount) * 100))
                    : 0;
                return $b;
            });

        $totalBudget = $budgets->sum('amount');
        $totalSpent  = $budgets->sum('spent');
        $totalRemain = max($totalBudget - $totalSpent, 0);

        /*
        |--------------------------------------------------------------------------
        | GOAL
        |--------------------------------------------------------------------------
        */
        $goals = BudgetGoal::where('user_id', $userId)
            ->where('type', 'goal')
            ->withSum(
                ['transactions as saved' => fn ($q) => $q->where('type', 'income')],
                'amount'
            )
            ->get()
            ->map(function ($g) {
                $g->target  = (int) $g->target_amount;
                $g->saved   = (int) ($g->saved ?? 0);
                $g->percent = $g->target > 0
                    ? min(100, round(($g->saved / $g->target) * 100))
                    : 0;
                return $g;
            });

        /*
        |--------------------------------------------------------------------------
        | DAILY (FIXED)
        |--------------------------------------------------------------------------
        */
        $dailyDate = request('date')
            ? Carbon::parse(request('date'))
            : now();

        $dailyFrom = $dailyDate->copy()->startOfDay();
        $dailyTo   = $dailyDate->copy()->endOfDay();

        $dailyRows = Transaction::with('category')
            ->where('user_id', $userId)
            ->whereBetween('date', [$dailyFrom, $dailyTo])
            ->orderBy('date')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | DAILY SUMMARY (REAL DATA)
        |--------------------------------------------------------------------------
        */
        $dailyIncome  = (int) $dailyRows->where('type', 'income')->sum('amount');
        $dailyExpense = (int) $dailyRows->where('type', 'expense')->sum('amount');
        $dailySelisih = $dailyIncome - $dailyExpense;


        return compact(
            // cashflow
            'labels',
            'income',
            'expense',
            'totalIncome',
            'totalExpense',
            'selisih',

            // budget & goal
            'budgets',
            'goals',
            'totalBudget',
            'totalSpent',
            'totalRemain',

            // daily
            'date',
            'dailyRows',
            'dailyIncome',
            'dailyExpense',
            'dailySelisih',

            // filter
            'from',
            'to'
        );
    }
}
