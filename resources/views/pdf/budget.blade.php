<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fintrack - Budget Report</title>
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
        .green { color: #16a34a; font-weight: bold; }
        .yellow { color: #eab308; font-weight: bold; }
        .red { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Fintrack – Budget Report</div>
        <div class="subtitle">
            User: {{ $user->name ?? $user->email }}
        </div>
    </div>

    <table class="summary-table">
        <tr>
            <th>Total Budget</th>
            <th>Used</th>
            <th>Remaining</th>
        </tr>
        <tr>
            <td class="text-right">
                Rp {{ number_format($summary['total_limit'] ?? 0, 0, '.', ',') }}
            </td>
            <td class="text-right">
                Rp {{ number_format($summary['total_used'] ?? 0, 0, '.', ',') }}
            </td>
            <td class="text-right">
                Rp {{ number_format($summary['remaining'] ?? 0, 0, '.', ',') }}
            </td>
        </tr>
    </table>

    <h3>Detail Budget</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Nama Budget</th>
                <th>Kategori</th>
                <th class="text-right">Limit</th>
                <th class="text-right">Terpakai</th>
                <th class="text-right">Sisa</th>
                <th class="text-right">Usage %</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($rows as $row)
            @php
                $u = $row['usage'];
                $class = $u < 50 ? 'red' : ($u < 90 ? 'yellow' : 'green');
            @endphp
            <tr>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['category'] }}</td>
                <td class="text-right">
                    Rp {{ number_format($row['limit'], 0, '.', ',') }}
                </td>
                <td class="text-right">
                    Rp {{ number_format($row['used'], 0, '.', ',') }}
                </td>
                <td class="text-right">
                    Rp {{ number_format($row['remaining'], 0, '.', ',') }}
                </td>
                <td class="text-right {{ $class }}">
                    {{ number_format($row['usage'], 2, '.', ',') }}%
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">Belum ada data budget.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
