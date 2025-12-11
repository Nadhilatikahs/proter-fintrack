<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Flow Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin: 12px 0 4px; }
        .meta { font-size: 10px; color: #4b5563; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 5px 6px; text-align: left; }
        th { background: #e5e7eb; font-weight: 600; }
        .text-right { text-align: right; }
        .summary { margin-top: 10px; }
        .summary div { margin-bottom: 3px; }
    </style>
</head>
<body>
    <h1>Cash Flow Report</h1>
    <div class="meta">
        Period: {{ $from }} s/d {{ $to }}<br>
        Exported at: {{ $exportedAt }}
    </div>

    <h2>Summary</h2>
    <div class="summary">
        <div>Total Income: Rp {{ number_format($incomeTotal, 0, ',', '.') }}</div>
        <div>Total Expense: Rp {{ number_format($expenseTotal, 0, ',', '.') }}</div>
        <div>Net Cash Flow: Rp {{ number_format($diff, 0, ',', '.') }}</div>
    </div>

    <h2>Daily Details</h2>
    <table>
        <thead>
        <tr>
            <th>Date</th>
            <th class="text-right">Income</th>
            <th class="text-right">Expense</th>
            <th class="text-right">Net</th>
        </tr>
        </thead>
        <tbody>
        @forelse($rows as $row)
            <tr>
                <td>{{ $row->date }}</td>
                <td class="text-right">Rp {{ number_format($row->income, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($row->expense, 0, ',', '.') }}</td>
                <td class="text-right">
                    Rp {{ number_format(($row->income - $row->expense), 0, ',', '.') }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No data for this period.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
