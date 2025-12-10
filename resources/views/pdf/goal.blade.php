<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fintrack - Goals Report</title>
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
        .table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .table th, .table td { padding: 4px 6px; border: 1px solid #d4d4d4; }
        .table th { background: #bbf7d0; }
        .text-right { text-align: right; }
        .status-done { color: #16a34a; font-weight: bold; }
        .status-progress { color: #eab308; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Fintrack – Goals Report</div>
        <div class="subtitle">
            User: {{ $user->name ?? $user->email }}
        </div>
    </div>

    <table class="summary-table">
        <tr>
            <th>Total Goals</th>
            <th>Achieved</th>
            <th>In Progress</th>
        </tr>
        <tr>
            <td>{{ $summary['total'] ?? 0 }}</td>
            <td class="status-done">{{ $summary['done'] ?? 0 }}</td>
            <td class="status-progress">{{ $summary['running'] ?? 0 }}</td>
        </tr>
    </table>

    <h3>Detail Goals</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Nama Goal</th>
                <th>Kategori</th>
                <th class="text-right">Target</th>
                <th class="text-right">Terkumpul</th>
                <th>Deadline</th>
                <th class="text-right">Progress</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($rows as $row)
            @php
                $statusClass = $row['status'] === 'Achieved' ? 'status-done' : 'status-progress';
            @endphp
            <tr>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['category'] }}</td>
                <td class="text-right">
                    Rp {{ number_format($row['target'], 0, '.', ',') }}
                </td>
                <td class="text-right">
                    Rp {{ number_format($row['saved'], 0, '.', ',') }}
                </td>
                <td>{{ $row['due_date'] ?? '-' }}</td>
                <td class="text-right">
                    {{ number_format($row['progress'], 2, '.', ',') }}%
                </td>
                <td class="{{ $statusClass }}">{{ $row['status'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7">Belum ada data goals.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
