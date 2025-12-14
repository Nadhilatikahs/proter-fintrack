<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Flow Report</title>

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

        .period {
            font-size: 12px;
            margin-bottom: 16px;
            color: #374151;
        }

        .summary {
            width: 100%;
            margin-bottom: 16px;
        }

        .summary td {
            padding: 10px;
            font-weight: bold;
            border-radius: 6px;
        }

        .green { background: #d1fae5; }
        .red   { background: #fee2e2; }
        .blue  { background: #e0f2fe; }

        table {
            width: 100%;
            border-collapse: collapse;
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
    </style>
</head>
<body>

    <h1>Cash Flow Report</h1>

    <div class="period">
        Period:
        {{ $from->format('d M Y') }}
        —
        {{ $to->format('d M Y') }}
    </div>

    {{-- SUMMARY --}}
    <table class="summary">
        <tr>
            <td class="green">Income<br>Rp {{ number_format($totalIncome,0,',','.') }}</td>
            <td class="red">Expense<br>Rp {{ number_format($totalExpense,0,',','.') }}</td>
            <td class="blue">Difference<br>Rp {{ number_format($selisih,0,',','.') }}</td>
        </tr>
    </table>

    {{-- TABLE --}}
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th class="right">Income</th>
                <th class="right">Expense</th>
            </tr>
        </thead>
        <tbody>
            @foreach($labels as $i => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td class="right">
                        Rp {{ number_format($income[$i],0,',','.') }}
                    </td>
                    <td class="right">
                        Rp {{ number_format($expense[$i],0,',','.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
