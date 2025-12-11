<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Goals Report</title>
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
    <h1>Goals Report</h1>
    <div class="meta">
        Exported at: {{ $exportedAt }}
    </div>

    <h2>Summary</h2>
    <div class="summary">
        <div>Goals Achieved: {{ $done }}</div>
        <div>Goals In Progress: {{ $running }}</div>
        <div>Total Goals: {{ $total }}</div>
    </div>

    <h2>Goal Details</h2>
    <table>
        <thead>
        <tr>
            <th>Name</th>
            <th class="text-right">Target Amount</th>
            <th>Target Date</th>
            <th class="text-right">Progress</th>
        </tr>
        </thead>
        <tbody>
        @forelse($goals as $goal)
            <tr>
                <td>{{ $goal->name }}</td>
                <td class="text-right">
                    Rp {{ number_format($goal->target_amount ?? 0, 0, ',', '.') }}
                </td>
                <td>{{ optional($goal->target_date)->format('d M Y') ?? '-' }}</td>
                <td class="text-right">{{ $goal->progress ?? 0 }}%</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No goals defined.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
