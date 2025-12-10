<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use App\Models\BudgetGoal;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Reports extends Page
{
    protected static ?string $title           = 'Reports';
    protected static ?string $navigationGroup = 'MENU';
    protected static ?string $navigationIcon  = 'heroicon-o-chart-pie';
    protected static ?int    $navigationSort  = 40;
    protected static ?string $navigationLabel = 'Reports';

    // View khusus untuk page ini
    protected static string $view = 'filament.pages.reports';

    /** @var string cash-flow|budget|daily|goal */
    public string $tab = 'cash-flow';

    /** mode tampilan (nanti bisa kita pakai) */
    public string $periodMode = 'month'; // day | month | year

    // Filter tanggal global untuk semua report
    public ?string $fromDate = null;
    public ?string $toDate   = null;

    public function mount(): void
    {
        // default: 1 bulan berjalan
        $today = Carbon::today();

        $this->fromDate = $today->copy()->startOfMonth()->toDateString();
        $this->toDate   = $today->copy()->endOfMonth()->toDateString();
    }

    /**
     * Dipanggil saat klik tombol "Apply Filter".
     * Tidak perlu isi apa-apa: Livewire akan re-render dan memanggil getViewData().
     */
    public function applyFilters(): void
    {
        // cukup kosong
    }

    /**
     * Data yang dikirim ke Blade view.
     */
    protected function getViewData(): array
    {
        return [
            'cashFlow' => $this->getCashFlowData(),
            'budget'   => $this->getBudgetData(),
            'daily'    => $this->getDailyData(),
            'goals'    => $this->getGoalsData(),
        ];
    }

    /**
     * Helper: ambil range tanggal yang valid
     */
    protected function getDateRange(): array
    {
        if ($this->fromDate && $this->toDate) {
            return [$this->fromDate, $this->toDate];
        }

        $today = Carbon::today();

        return [
            $today->copy()->startOfMonth()->toDateString(),
            $today->copy()->endOfMonth()->toDateString(),
        ];
    }

    /**
     * CASH FLOW
     * Line chart income vs expense + total summary.
     */
    protected function getCashFlowData(): array
    {
        $userId = Auth::id();
        [$start, $end] = $this->getDateRange();

        if (! $userId) {
            return [
                'labels'  => [],
                'income'  => [],
                'expense' => [],
                'total'   => ['income' => 0, 'expense' => 0, 'diff' => 0],
            ];
        }

        $rows = Transaction::query()
            ->selectRaw('date, type, SUM(amount) AS total')
            ->where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get();

        $dates   = [];
        $income  = [];
        $expense = [];

        $incomeTotal  = 0;
        $expenseTotal = 0;

        // susun per tanggal
        $grouped = $rows->groupBy('date');
        foreach ($grouped as $date => $items) {
            $dates[] = $date;

            $incomeVal  = (float) ($items->firstWhere('type', 'income')->total ?? 0);
            $expenseVal = (float) ($items->firstWhere('type', 'expense')->total ?? 0);

            $income[]  = $incomeVal;
            $expense[] = $expenseVal;

            $incomeTotal  += $incomeVal;
            $expenseTotal += $expenseVal;
        }

        return [
            'labels'  => $dates,
            'income'  => $income,
            'expense' => $expense,
            'total'   => [
                'income'  => $incomeTotal,
                'expense' => $expenseTotal,
                'diff'    => $incomeTotal - $expenseTotal,
            ],
        ];
    }

    /**
     * BUDGET
     * Bar chart horizontal per budget + ringkasan total.
     */
    protected function getBudgetData(): array
    {
        $userId = Auth::id();

        if (! $userId) {
            return [
                'labels'  => [],
                'limit'   => [],
                'used'    => [],
                'summary' => [
                    'total_limit' => 0,
                    'total_used'  => 0,
                    'remaining'   => 0,
                ],
            ];
        }

        $budgets = BudgetGoal::query()
            ->where('user_id', $userId)
            ->where('type', 'budget')
            ->whereNotNull('period_type')
            ->get();

        $labels      = [];
        $limitValues = [];
        $usedValues  = [];

        $totalLimit = 0;
        $totalUsed  = 0;

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

    /**
     * DAILY
     * Laporan per hari (income, expense, net).
     */
    protected function getDailyData(): array
    {
        $userId = Auth::id();
        [$start, $end] = $this->getDateRange();

        if (! $userId) {
            return [
                'labels'  => [],
                'income'  => [],
                'expense' => [],
                'net'     => [],
            ];
        }

        $rows = Transaction::query()
            ->selectRaw('date,
                SUM(CASE WHEN type = "income"  THEN amount ELSE 0 END) AS income,
                SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) AS expense
            ')
            ->where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
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

    /**
     * GOAL
     * Hitung berapa goal yang sudah tercapai & belum.
     */
    protected function getGoalsData(): array
    {
        $userId = Auth::id();

        if (! $userId) {
            return [
                'done'    => 0,
                'running' => 0,
                'total'   => 0,
            ];
        }

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

    /**
     * Helper: hitung pemakaian budget (reuse dari BudgetStatusWidget).
     */
    protected function calculateBudgetSpent(BudgetGoal $budget): float
    {
        $userId = $budget->user_id;
        $now    = Carbon::now();

        [$start, $end] = $this->getPeriodRange($budget->period_type, $now);

        return Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');
    }

    /**
     * Helper: progress goal (contoh sederhana).
     */
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

    /**
     * Periode budget (sama seperti di widget).
     */
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

    /**
     * Untuk sekarang, kita tidak pakai header actions lama (export terpisah),
     * karena export PDF akan kita handle lewat tombol di dalam view.
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
