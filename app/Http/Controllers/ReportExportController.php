<?php

namespace App\Http\Controllers;

use App\Models\BudgetGoal;
use App\Models\Category;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportExportController extends Controller
{
    public function export(string $type, Request $request)
    {
        $user = Auth::user();

        // Ambil range tanggal dari query (kalau ada), default 1 bulan terakhir
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : now()->startOfMonth();

        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfMonth();

        switch ($type) {
            case 'cash-flow':
                $data = $this->buildCashFlowData($user->id, $from, $to);
                $view = 'reports.pdf.cash_flow';
                $filename = 'fintrack-cash-flow-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.pdf';
                break;

            case 'daily':
                $data = $this->buildDailyTransactionsData($user->id, $from, $to);
                $view = 'reports.pdf.daily';
                $filename = 'fintrack-daily-transactions-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.pdf';
                break;

            case 'budget':
                $data = $this->buildBudgetData($user->id);
                $view = 'reports.pdf.budget';
                $filename = 'fintrack-budget-report.pdf';
                break;

            case 'goal':
                $data = $this->buildGoalData($user->id);
                $view = 'reports.pdf.goal';
                $filename = 'fintrack-goals-report.pdf';
                break;

            default:
                abort(404);
        }

        // Tambahkan info umum (user + periode) ke data
        $data['user'] = $user;
        $data['from'] = $from;
        $data['to']   = $to;

        $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    /* =========================
     * DATA BUILDER PER REPORT
     * ========================= */

    protected function buildCashFlowData(int $userId, Carbon $from, Carbon $to): array
    {
        $transactions = Transaction::where('user_id', $userId)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')
            ->get();

        $grouped = $transactions->groupBy('date');

        $rows = [];
        $totalIncome = 0;
        $totalExpense = 0;

        foreach ($grouped as $date => $items) {
            $income = $items->where('type', 'income')->sum('amount');
            $expense = $items->where('type', 'expense')->sum('amount');
            $diff = $income - $expense;

            $totalIncome += $income;
            $totalExpense += $expense;

            $rows[] = [
                'date'    => Carbon::parse($date)->format('d M Y'),
                'income'  => $income,
                'expense' => $expense,
                'diff'    => $diff,
            ];
        }

        return [
            'rows'  => $rows,
            'total' => [
                'income'  => $totalIncome,
                'expense' => $totalExpense,
                'diff'    => $totalIncome - $totalExpense,
            ],
        ];
    }

    protected function buildDailyTransactionsData(int $userId, Carbon $from, Carbon $to): array
    {
        $transactions = Transaction::with('category')
            ->where('user_id', $userId)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')
            ->orderBy('created_at')
            ->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');

        return [
            'transactions' => $transactions,
            'summary' => [
                'count'   => $transactions->count(),
                'income'  => $totalIncome,
                'expense' => $totalExpense,
                'net'     => $totalIncome - $totalExpense,
            ],
        ];
    }

    protected function buildBudgetData(int $userId): array
    {
        $budgets = BudgetGoal::with('category')
            ->where('user_id', $userId)
            ->where('type', 'budget')
            ->get();

        $rows = [];
        $totalLimit = 0;
        $totalUsed = 0;

        foreach ($budgets as $budget) {
            $used = $budget->transactions()
                ->where('type', 'expense')
                ->sum('amount');

            $limit = $budget->target_amount ?? 0;
            $remaining = max(0, $limit - $used);
            $usage = $limit > 0 ? round($used / $limit * 100, 2) : 0;

            $totalLimit += $limit;
            $totalUsed += $used;

            $rows[] = [
                'name'      => $budget->name,
                'category'  => optional($budget->category)->name ?? '-',
                'limit'     => $limit,
                'used'      => $used,
                'remaining' => $remaining,
                'usage'     => $usage,
            ];
        }

        return [
            'rows' => $rows,
            'summary' => [
                'total_limit' => $totalLimit,
                'total_used'  => $totalUsed,
                'remaining'   => max(0, $totalLimit - $totalUsed),
            ],
        ];
    }

    protected function buildGoalData(int $userId): array
    {
        $goals = BudgetGoal::with('category')
            ->where('user_id', $userId)
            ->where('type', 'goal')
            ->get();

        $rows = [];
        $total = $goals->count();
        $done = 0;

        foreach ($goals as $goal) {
            // contoh logika progress: jumlah semua income ke kategori goal
            $saved = $goal->transactions()
                ->where('type', 'income')
                ->sum('amount');

            $target = $goal->target_amount ?? 0;
            $progress = $target > 0 ? round($saved / $target * 100, 2) : 0;
            $status = $progress >= 100 ? 'Achieved' : 'In progress';

            if ($progress >= 100) {
                $done++;
            }

            $rows[] = [
                'name'      => $goal->name,
                'category'  => optional($goal->category)->name ?? '-',
                'target'    => $target,
                'saved'     => $saved,
                'progress'  => $progress,
                'status'    => $status,
                'due_date'  => optional($goal->due_date ? Carbon::parse($goal->due_date) : null)?->format('d M Y'),
            ];
        }

        return [
            'rows' => $rows,
            'summary' => [
                'total'   => $total,
                'done'    => $done,
                'running' => max(0, $total - $done),
            ],
        ];
    }
}
