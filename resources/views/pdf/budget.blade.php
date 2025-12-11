<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Budget Report</title>
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
    <h1>Budget Report</h1>
    <div class="meta">
        Exported at: {{ $exportedAt }}
    </div>

    <h2>Summary</h2>
    <div class="summary">
        <div>Total Budget: Rp {{ number_format($totalLimit, 0, ',', '.') }}</div>
        <div>Total Spent: Rp {{ number_format($totalUsed, 0, ',', '.') }}</div>
        <div>Remaining Budget: Rp {{ number_format($remaining, 0, ',', '.') }}</div>
    </div>

    <h2>Budget Details</h2>
    <table>
        <thead>
        <tr>
            <th>Name</th>
            <th>Period</th>
            <th class="text-right">Limit</th>
            <th class="text-right">Spent</th>
            <th class="text-right">Progress</th>
        </tr>
        </thead>
        <tbody>
        @forelse($budgets as $budget)
            <tr>
                <td>{{ $budget->name }}</td>
                <td>{{ $budget->period_type ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($budget->target_amount ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($budget->spent ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ $budget->progress ?? 0 }}%</td>
            </tr>
        @empty
            <tr>
                <td colspan="5">No budget defined.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
