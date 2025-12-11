<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use App\Models\BudgetGoal;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Reports extends Page
{
    protected static ?string $title           = 'Reports';
    protected static ?string $navigationGroup = 'MENU';
    protected static ?string $navigationIcon  = 'heroicon-o-chart-pie';
    protected static ?int    $navigationSort  = 40;
    protected static ?string $navigationLabel = 'Reports';

    protected static string $view = 'filament.pages.reports';

    /** @var string cash-flow|budget|daily|goal */
    public string $tab = 'cash-flow';

    // filter tanggal global (dipakai cash-flow & daily)
    public ?string $fromDate = null;
    public ?string $toDate   = null;

    public function mount(): void
    {
        $today          = Carbon::today();
        $this->fromDate = $today->copy()->startOfMonth()->toDateString();
        $this->toDate   = $today->copy()->endOfMonth()->toDateString();
    }

    protected function getViewData(): array
    {
        return [
            'cashFlow' => $this->getCashFlowData(),
            'budget'   => $this->getBudgetData(),
            'daily'    => $this->getDailyData(),
            'goals'    => $this->getGoalsData(),
        ];
    }

    /** CASH FLOW: income vs expense per hari + total summary */
    protected function getCashFlowData(): array
    {
        $userId = Auth::id();

        if (! $userId || ! $this->fromDate || ! $this->toDate) {
            return [
                'labels'  => [],
                'income'  => [],
                'expense' => [],
                'total'   => ['income' => 0, 'expense' => 0, 'diff' => 0],
            ];
        }

        $rows = Transaction::query()
            ->selectRaw('date,
                SUM(CASE WHEN type = "income"  THEN amount ELSE 0 END) AS income,
                SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) AS expense
            ')
            ->where('user_id', $userId)
            ->whereBetween('date', [$this->fromDate, $this->toDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels       = [];
        $incomeSeries = [];
        $expenseSeries = [];
        $incomeTotal  = 0;
        $expenseTotal = 0;

        foreach ($rows as $row) {
            $labels[]        = $row->date;
            $incomeSeries[]  = (float) $row->income;
            $expenseSeries[] = (float) $row->expense;

            $incomeTotal  += (float) $row->income;
            $expenseTotal += (float) $row->expense;
        }

        return [
            'labels'  => $labels,
            'income'  => $incomeSeries,
            'expense' => $expenseSeries,
            'total'   => [
                'income'  => $incomeTotal,
                'expense' => $expenseTotal,
                'diff'    => $incomeTotal - $expenseTotal,
            ],
        ];
    }

    /** BUDGET: progress tiap budget + summary total */
    protected function getBudgetData(): array
    {
        $userId = Auth::id();

        $budgets = BudgetGoal::query()
            ->where('user_id', $userId)
            ->where('type', 'budget')
            ->whereNotNull('period_type')
            ->get();

        $labels      = [];
        $limitValues = [];
        $usedValues  = [];
        $totalLimit  = 0;
        $totalUsed   = 0;

        foreach ($budgets as $budget) {
            $labels[] = $budget->name;

            $limit = (float) $budget->target_amount;
            $used  = (float) $this->calculateBudgetSpent($budget);

            $limitValues[] = $limit;
            $usedValues[]  = $used;

            $totalLimit += $limit;
            $totalUsed  += $used;
        }

        return [
            'labels'  => $labels,
            'limit'   => $limitValues,
            'used'    => $usedValues,
            'summary' => [
                'total_limit' => $totalLimit,
                'total_used'  => $totalUsed,
                'remaining'   => max(0, $totalLimit - $totalUsed),
            ],
        ];
    }

    /** DAILY: income / expense / net per hari */
    protected function getDailyData(): array
    {
        $userId = Auth::id();

        if (! $userId || ! $this->fromDate || ! $this->toDate) {
            return ['labels' => [], 'income' => [], 'expense' => [], 'net' => []];
        }

        $rows = Transaction::query()
            ->selectRaw('date,
                SUM(CASE WHEN type = "income"  THEN amount ELSE 0 END) AS income,
                SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) AS expense
            ')
            ->where('user_id', $userId)
            ->whereBetween('date', [$this->fromDate, $this->toDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels  = [];
        $income  = [];
        $expense = [];
        $net     = [];

        foreach ($rows as $row) {
            $labels[]  = $row->date;
            $income[]  = (float) $row->income;
            $expense[] = (float) $row->expense;
            $net[]     = (float) $row->income - (float) $row->expense;
        }

        return compact('labels', 'income', 'expense', 'net');
    }

    /** GOALS: jumlah goal selesai vs belum */
    protected function getGoalsData(): array
    {
        $userId = Auth::id();

        $goals = BudgetGoal::query()
            ->where('user_id', $userId)
            ->where('type', 'goal')
            ->get();

        $countDone    = 0;
        $countRunning = 0;

        foreach ($goals as $goal) {
            $progress = $this->calculateGoalProgress($goal);

            if ($progress >= 100) {
                $countDone++;
            } else {
                $countRunning++;
            }
        }

        return [
            'done'    => $countDone,
            'running' => $countRunning,
            'total'   => $countDone + $countRunning,
        ];
    }

    /** Helper budget spent */
    protected function calculateBudgetSpent(BudgetGoal $budget): float
    {
        $userId = $budget->user_id;
        $now    = Carbon::now();

        [$start, $end] = $this->getPeriodRange($budget->period_type ?? 'monthly', $now);

        return Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');
    }

    /** Helper goal progress */
    protected function calculateGoalProgress(BudgetGoal $goal): float
    {
        if ($goal->target_amount <= 0) {
            return 0;
        }

        $saved = Transaction::query()
            ->where('user_id', $goal->user_id)
            ->where('type', 'income')
            ->when($goal->category_id ?? null, fn ($q, $categoryId) =>
                $q->where('category_id', $categoryId)
            )
            ->sum('amount');

        return round(($saved / $goal->target_amount) * 100, 1);
    }

    protected function getPeriodRange(string $periodType, Carbon $now): array
    {
        $start = $now->copy();
        $end   = $now->copy();

        return match ($periodType) {
            'daily'    => [$start->startOfDay(), $end->endOfDay()],
            'weekly'   => [$start->startOfWeek(), $end->endOfWeek()],
            'biweekly' => [$start->copy()->subDays(13)->startOfDay(), $end->endOfDay()],
            'yearly'   => [$start->startOfYear(), $end->endOfYear()],
            default    => [$start->startOfMonth(), $end->endOfMonth()],
        };
    }

    /** Actions di header untuk Export PDF */
    protected function getHeaderActions(): array
    {
        $from = $this->fromDate;
        $to   = $this->toDate;

        return [
            Action::make('exportCashFlow')
                ->label('Export Cash Flow PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn () => route('admin.reports.export', [
                    'type' => 'cash-flow',
                    'from' => $from,
                    'to'   => $to,
                ]))
                ->openUrlInNewTab(),

            Action::make('exportDaily')
                ->label('Export Daily PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn () => route('admin.reports.export', [
                    'type' => 'daily',
                    'from' => $from,
                    'to'   => $to,
                ]))
                ->openUrlInNewTab(),

            Action::make('exportBudget')
                ->label('Export Budget PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn () => route('admin.reports.export', [
                    'type' => 'budget',
                ]))
                ->openUrlInNewTab(),

            Action::make('exportGoal')
                ->label('Export Goals PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn () => route('admin.reports.export', [
                    'type' => 'goal',
                ]))
                ->openUrlInNewTab(),
        ];
    }
}
