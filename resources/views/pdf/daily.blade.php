<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fintrack - Daily Transactions Report</title>
    <style>
        * { box-sizing: border-box; font-family: DejaVu Sans, sans-serif; }
        body { font-size: 12px; background: #f6ffe8; color: #111827; }
        .header { margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #a3e635; }
        .title { font-size: 20px; font-weight: 700; color: #166534; }
        .subtitle { font-size: 12px; margin-top: 4px; }
        .summary-table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        .summary-table th,
        .summary-table td { padding: 6px 8px; border: 1px solid #d4d4d4; text-align: left; }
        .summary-table th { background: #bef264; }
        .income { color: #16a34a; font-weight: bold; }
        .expense { color: #dc2626; font-weight: bold; }
        .net-positive { color: #16a34a; font-weight: bold; }
        .net-negative { color: #dc2626; font-weight: bold; }
        .table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .table th, .table td { padding: 4px 6px; border: 1px solid #d4d4d4; }
        .table th { background: #bbf7d0; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Fintrack – Daily Transactions Report</div>
        <div class="subtitle">
            Periode: {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}<br>
            User: {{ $user->name ?? $user->email }}
        </div>
    </div>

    <table class="summary-table">
        <tr>
            <th>Total Transaksi</th>
            <th>Total Income</th>
            <th>Total Expense</th>
            <th>Net</th>
        </tr>
        @php $net = $summary['net'] ?? 0; @endphp
        <tr>
            <td>{{ $summary['count'] ?? 0 }}</td>
            <td class="income">
                Rp {{ number_format($summary['income'] ?? 0, 0, '.', ',') }}
            </td>
            <td class="expense">
                Rp {{ number_format($summary['expense'] ?? 0, 0, '.', ',') }}
            </td>
            <td class="{{ $net >= 0 ? 'net-positive' : 'net-negative' }}">
                Rp {{ number_format($net, 0, '.', ',') }}
            </td>
        </tr>
    </table>

    <h3>Detail Transaksi</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Deskripsi</th>
                <th>Tipe</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($transactions as $tx)
            <tr>
                <td>{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}</td>
                <td>{{ optional($tx->category)->name ?? '-' }}</td>
                <td>{{ $tx->name }}</td>
                <td>{{ ucfirst($tx->type) }}</td>
                <td class="text-right">
                    Rp {{ number_format($tx->amount, 0, '.', ',') }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5">Belum ada transaksi.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
