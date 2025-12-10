<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        // ==== FILTER tanggal utk chart transaksi ====
        $mode = $request->get('mode', 'month'); // day | month | year
        $from = $request->get('from');
        $to   = $request->get('to');

        if ($mode === 'day') {
            $fromDate = $from ? Carbon::parse($from) : now()->startOfMonth();
            $toDate   = $to ? Carbon::parse($to) : now();
            $groupFormat = 'Y-m-d';
        } elseif ($mode === 'year') {
            $fromYear = $from ? (int)$from : now()->year;
            $toYear   = $to ? (int)$to : now()->year;
            $fromDate = Carbon::create($fromYear)->startOfYear();
            $toDate   = Carbon::create($toYear)->endOfYear();
            $groupFormat = 'Y';
        } else { // default month
            $fromDate = $from ? Carbon::parse($from)->startOfMonth() : now()->startOfYear();
            $toDate   = $to ? Carbon::parse($to)->endOfMonth()   : now()->endOfYear();
            $groupFormat = 'Y-m';
        }

        // ==== CASHFLOW chart (income vs expense) ====
        $cash = Transaction::selectRaw("DATE_FORMAT(date, '{$groupFormat}') as label")
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income")
            ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
            ->where('user_id', $userId)
            ->whereBetween('date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        $cashflowLabels  = $cash->pluck('label');
        $cashflowIncome  = $cash->pluck('income');
        $cashflowExpense = $cash->pluck('expense');

        // ==== summary bulan ini ====
        $startMonth = now()->startOfMonth();
        $endMonth   = now()->endOfMonth();

        $monthIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$startMonth, $endMonth])
            ->sum('amount');

        $monthExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$startMonth, $endMonth])
            ->sum('amount');

        $summary = [
            'income'  => $monthIncome,
            'expense' => $monthExpense,
            'balance' => $monthIncome - $monthExpense,
        ];

        // ==== PIE Spending by category ====
        $spending = Transaction::selectRaw('categories.name as category')
            ->selectRaw('SUM(transactions.amount) as total')
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.type', 'expense')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();

        $pieLabels = $spending->pluck('category');
        $pieData   = $spending->pluck('total');

        // ==== last transactions 10 data ====
        $lastTransactions = Transaction::with('category')
            ->where('user_id', $userId)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'cashflowLabels',
            'cashflowIncome',
            'cashflowExpense',
            'mode',
            'from',
            'to',
            'summary',
            'pieLabels',
            'pieData',
            'lastTransactions'
        ));
    }
}
