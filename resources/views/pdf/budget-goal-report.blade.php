<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Budget & Goal Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 4px;
        }

        h2 {
            font-size: 14px;
            margin: 16px 0 8px;
        }

        .period {
            font-size: 12px;
            margin-bottom: 12px;
            color: #374151;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        th, td {
            border: 1px solid #e5e7eb;
            padding: 6px;
        }

        th {
            background: #f3f4f6;
            text-align: left;
        }

        .right { text-align: right; }

        .danger { color: #b91c1c; font-weight: bold; }
        .warning { color: #b45309; font-weight: bold; }
        .safe { color: #047857; font-weight: bold; }
    </style>
</head>
<body>

    <h1>Budget & Goal Report</h1>

    <div class="period">
        Period:
        {{ $from->format('d M Y') }}
        —
        {{ $to->format('d M Y') }}
    </div>

    {{-- ================= BUDGET ================= --}}
    <h2>Budget</h2>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th class="right">Target</th>
                <th class="right">Used</th>
                <th class="right">Remaining</th>
                <th class="right">Progress</th>
            </tr>
        </thead>
        <tbody>
            @foreach($budgets as $b)
                <tr>
                    <td>{{ $b->name }}</td>
                    <td class="right">Rp {{ number_format($b->amount,0,',','.') }}</td>
                    <td class="right">Rp {{ number_format($b->spent,0,',','.') }}</td>
                    <td class="right">Rp {{ number_format($b->remaining,0,',','.') }}</td>
                    <td class="right {{ $b->status }}">
                        {{ $b->percent }}%
                        @if($b->percent >= 80)
                            ⚠
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ================= GOAL ================= --}}
    <h2>Goal</h2>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th class="right">Target</th>
                <th class="right">Current</th>
                <th class="right">Progress</th>
            </tr>
        </thead>
        <tbody>
            @foreach($goals as $g)
                <tr>
                    <td>{{ $g->name }}</td>
                    <td class="right">Rp {{ number_format($g->amount,0,',','.') }}</td>
                    <td class="right">Rp {{ number_format($g->current ?? 0,0,',','.') }}</td>
                    <td class="right {{ $g->status }}">
                        {{ $g->percent }}%
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
