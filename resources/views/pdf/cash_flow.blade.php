<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fintrack - Cash Flow Report</title>
    <style>
        * { box-sizing: border-box; font-family: DejaVu Sans, sans-serif; }
        body { font-size: 12px; background: #f6ffe8; color: #111827; }
        .header { margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #a3e635; }
        .title { font-size: 20px; font-weight: 700; color: #166534; }
        .subtitle { font-size: 12px; margin-top: 4px; }
        .summary { margin: 16px 0; }
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table th,
        .summary-table td { padding: 6px 8px; border: 1px solid #d4d4d4; text-align: left; }
        .summary-table th { background: #bef264; }
        .income { color: #16a34a; font-weight: bold; }
        .expense { color: #dc2626; font-weight: bold; }
        .diff-positive { color: #16a34a; font-weight: bold; }
        .diff-negative { color: #dc2626; font-weight: bold; }
        .table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .table th, .table td { padding: 6px 8px; border: 1px solid #d4d4d4; }
        .table th { background: #bbf7d0; }
        .text-right { text-align: right; }
        .mt-2 { margin-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Fintrack – Cash Flow Report</div>
        <div class="subtitle">
            Periode: {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}<br>
            User: {{ $user->name ?? $user->email }}
        </div>
    </div>

    <div class="summary">
        <table class="summary-table">
            <tr>
                <th>Total Income</th>
                <th>Total Expense</th>
                <th>Balance</th>
            </tr>
            <tr>
                <td class="income">
                    Rp {{ number_format($total['income'] ?? 0, 0, '.', ',') }}
                </td>
                <td class="expense">
                    Rp {{ number_format($total['expense'] ?? 0, 0, '.', ',') }}
                </td>
                @php $diff = $total['diff'] ?? 0; @endphp
                <td class="{{ $diff >= 0 ? 'diff-positive' : 'diff-negative' }}">
                    Rp {{ number_format($diff, 0, '.', ',') }}
                </td>
            </tr>
        </table>
    </div>

    <h3>Rincian Cash Flow per Hari</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th class="text-right">Income</th>
                <th class="text-right">Expense</th>
                <th class="text-right">Selisih</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($rows as $row)
            @php $d = $row['diff']; @endphp
            <tr>
                <td>{{ $row['date'] }}</td>
                <td class="text-right">
                    Rp {{ number_format($row['income'], 0, '.', ',') }}
                </td>
                <td class="text-right">
                    Rp {{ number_format($row['expense'], 0, '.', ',') }}
                </td>
                <td class="text-right {{ $d >= 0 ? 'diff-positive' : 'diff-negative' }}">
                    Rp {{ number_format($d, 0, '.', ',') }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="mt-2">Belum ada transaksi dalam periode ini.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

</body>
</html>
