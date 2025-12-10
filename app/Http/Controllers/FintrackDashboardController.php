<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FintrackDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // --- Bulan yang dipilih dari query string (?month=2025-12) ---
        $monthInput = $request->input('month'); // format "Y-m"
        if ($monthInput) {
            try {
                $currentMonth = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
            } catch (\Exception $e) {
                $currentMonth = now()->startOfMonth();
            }
        } else {
            $currentMonth = now()->startOfMonth();
        }

        $startDate = $currentMonth->copy();
        $endDate   = $currentMonth->copy()->endOfMonth();

        // Query dasar transaksi user bulan ini
        $baseQuery = Transaction::where('user_id', $user->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);

        // ---------------------------------------------------------------------
        // 1. Summary: Pemasukan, Pengeluaran, Saldo bulan ini
        // ---------------------------------------------------------------------
        $monthlyIncome  = (clone $baseQuery)->where('type', 'income')->sum('amount');
        $monthlyExpense = (clone $baseQuery)->where('type', 'expense')->sum('amount');
        $monthlyBalance = $monthlyIncome - $monthlyExpense;

        // ---------------------------------------------------------------------
        // 2. Spending by category (pie chart) – hanya pengeluaran
        // ---------------------------------------------------------------------
        $expensesByCategory = (clone $baseQuery)
            ->where('type', 'expense')
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category')
            ->get();

        $spendLabels = $expensesByCategory->map(function ($row) {
            return optional($row->category)->name ?: 'Uncategorized';
        })->values();

        $spendData = $expensesByCategory->map(function ($row) {
            return (float) $row->total;
        })->values();

        // Kalau belum ada data sama sekali, isi placeholder biar chart nggak error
        if ($spendLabels->isEmpty()) {
            $spendLabels = collect(['No data']);
            $spendData   = collect([0]);
        }

        // ---------------------------------------------------------------------
        // 3. Cashflow overview (line chart) – income vs expense per minggu
        // ---------------------------------------------------------------------
        $weeklyIncome  = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        $weeklyExpense = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

        $allThisMonth = (clone $baseQuery)->get();

        foreach ($allThisMonth as $tx) {
            $week = Carbon::parse($tx->date)->weekOfMonth;
            if ($week < 1) $week = 1;
            if ($week > 4) $week = 4; // cukup 4 minggu untuk tampilan

            if ($tx->type === 'income') {
                $weeklyIncome[$week] += $tx->amount;
            } else {
                $weeklyExpense[$week] += $tx->amount;
            }
        }

        $weeksLabels   = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
        $cashIncome    = array_values($weeklyIncome);
        $cashExpense   = array_values($weeklyExpense);

        // ---------------------------------------------------------------------
        // 4. Transactions list (history) – 6 transaksi terbaru bulan ini
        // ---------------------------------------------------------------------
        $transactions = (clone $baseQuery)
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        // Untuk highlight tanggal yang punya transaksi di kalender
        $transactionDays = (clone $baseQuery)
            ->selectRaw('DATE(date) as day')
            ->groupBy('day')
            ->pluck('day')
            ->map(function ($d) {
                return Carbon::parse($d)->day;
            })
            ->toArray();

        return view('fintrack.dashboard', [
            'user'              => $user,
            'currentMonth'      => $currentMonth,
            'monthlyIncome'     => $monthlyIncome,
            'monthlyExpense'    => $monthlyExpense,
            'monthlyBalance'    => $monthlyBalance,
            'spendLabels'       => $spendLabels,
            'spendData'         => $spendData,
            'weeksLabels'       => $weeksLabels,
            'cashIncome'        => $cashIncome,
            'cashExpense'       => $cashExpense,
            'transactions'      => $transactions,
            'transactionDays'   => $transactionDays,
        ]);
    }
}
