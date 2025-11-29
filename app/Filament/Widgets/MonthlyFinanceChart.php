<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class MonthlyFinanceChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Pemasukan & Pengeluaran (Tahun ini)';

    protected function getData(): array
    {
        $year = Carbon::now()->year;

        // Siapkan array 12 bulan dengan default 0
        $incomePerMonth  = array_fill(1, 12, 0);
        $expensePerMonth = array_fill(1, 12, 0);

        // Ambil semua transaksi tahun ini
        $transactions = Transaction::with('category')
            ->whereYear('date', $year)
            ->get();

        foreach ($transactions as $transaction) {
            $month = Carbon::parse($transaction->date)->month;

            if ($transaction->category?->type === 'income') {
                $incomePerMonth[$month] += (float) $transaction->amount;
            }

            if ($transaction->category?->type === 'expense') {
                $expensePerMonth[$month] += (float) $transaction->amount;
            }
        }

        // Label bulan (bisa pakai singkatan biar pendek)
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data'  => array_values($incomePerMonth),
                ],
                [
                    'label' => 'Pengeluaran',
                    'data'  => array_values($expensePerMonth),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        // Bisa 'line', 'bar', dll.
        return 'bar';
    }
}
