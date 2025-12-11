<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\BudgetGoal;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportExportController extends Controller
{
    public function export(Request $request)
    {
        $type = $request->query('type', 'cash-flow');
        $from = $request->query('from');
        $to   = $request->query('to');

        $userId = Auth::id();

        if (! $userId) {
            abort(403);
        }

        // Normalisasi tanggal
        if ($from) {
            $fromDate = Carbon::parse($from)->toDateString();
        } else {
            $fromDate = Carbon::now()->startOfMonth()->toDateString();
        }

        if ($to) {
            $toDate = Carbon::parse($to)->toDateString();
        } else {
            $toDate = Carbon::now()->endOfMonth()->toDateString();
        }

        switch ($type) {
            case 'cash-flow':
                $data     = $this->buildCashFlowData($userId, $fromDate, $toDate);
                $view     = 'reports.pdf.cash-flow';
                $filename = "cash-flow-{$fromDate}-{$toDate}.pdf";
                break;

            case 'daily':
                $data     = $this->buildDailyData($userId, $fromDate, $toDate);
                $view     = 'reports.pdf.daily';
                $filename = "daily-report-{$fromDate}-{$toDate}.pdf";
                break;

            case 'budget':
                $data     = $this->buildBudgetData($userId);
                $view     = 'reports.pdf.budget';
                $filename = "budget-report.pdf";
                break;

            case 'goal':
                $data     = $this->buildGoalsData($userId);
                $view     = 'reports.pdf.goals';
                $filename = "goals-report.pdf";
                break;

            default:
                abort(404);
        }

        $pdf = Pdf::loadView($view, array_merge($data, [
            'exportedAt' => now(),
        ]));

        return $pdf->download($filename);
    }

    protected function buildCashFlowData(int $userId, string $from, string $to): array
    {
        $rows = Transaction::query()
            ->selectRaw('date,
                SUM(CASE WHEN type = "income"  THEN amount ELSE 0 END) AS income,
                SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) AS expense
            ')
            ->where('user_id', $userId)
            ->whereBetween('date', [$from, $to])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $incomeTotal  = 0;
        $expenseTotal = 0;

        foreach ($rows as $row) {
            $incomeTotal  += (float) $row->income;
            $expenseTotal += (float) $row->expense;
        }

        return [
            'from'         => $from,
            'to'           => $to,
            'rows'         => $rows,
            'incomeTotal'  => $incomeTotal,
            'expenseTotal' => $expenseTotal,
            'diff'         => $incomeTotal - $expenseTotal,
        ];
    }

    protected function buildDailyData(int $userId, string $from, string $to): array
    {
        $rows = Transaction::query()
            ->selectRaw('date,
                SUM(CASE WHEN type = "income"  THEN amount ELSE 0 END) AS income,
                SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) AS expense
            ')
            ->where('user_id', $userId)
            ->whereBetween('date', [$from, $to])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'from' => $from,
            'to'   => $to,
            'rows' => $rows,
        ];
    }

    protected function buildBudgetData(int $userId): array
    {
        $budgets = BudgetGoal::query()
            ->where('user_id', $userId)
            ->where('type', 'budget')
            ->get();

        $totalLimit = 0;
        $totalUsed  = 0;

        foreach ($budgets as $budget) {
            $spent = $this->calculateBudgetSpent($budget);
            $budget->spent    = $spent;
            $budget->progress = $budget->target_amount > 0
                ? round(min(100, ($spent / $budget->target_amount) * 100))
                : 0;

            $totalLimit += (float) $budget->target_amount;
            $totalUsed  += $spent;
        }

        return [
            'budgets'        => $budgets,
            'totalLimit'     => $totalLimit,
            'totalUsed'      => $totalUsed,
            'remaining'      => max(0, $totalLimit - $totalUsed),
        ];
    }

    protected function buildGoalsData(int $userId): array
    {
        $goals = BudgetGoal::query()
            ->where('user_id', $userId)
            ->where('type', 'goal')
            ->get();

        $countDone    = 0;
        $countRunning = 0;

        foreach ($goals as $goal) {
            $progress = $this->calculateGoalProgress($goal);
            $goal->progress = $progress;

            if ($progress >= 100) {
                $countDone++;
            } else {
                $countRunning++;
            }
        }

        return [
            'goals'   => $goals,
            'done'    => $countDone,
            'running' => $countRunning,
            'total'   => $countDone + $countRunning,
        ];
    }

    protected function calculateBudgetSpent(BudgetGoal $budget): float
    {
        $now = Carbon::now();

        [$start, $end] = $this->getPeriodRange($budget->period_type ?? 'monthly', $now);

        return Transaction::where('user_id', $budget->user_id)
            ->where('type', 'expense')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');
    }

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
}
