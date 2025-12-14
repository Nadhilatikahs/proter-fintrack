<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 6px;
        }

        .date {
            font-size: 12px;
            margin-bottom: 14px;
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
        .income { color: #047857; }
        .expense { color: #b91c1c; }
    </style>
</head>
<body>

    <h1>Daily Report</h1>

    <div class="date">
        Date: {{ $date->format('d M Y') }}
    </div>

    {{-- SUMMARY --}}
    <table class="summary">
        <tr>
            <td class="green">
                Income<br>
                Rp {{ number_format($totalIncome,0,',','.') }}
            </td>
            <td class="red">
                Expense<br>
                Rp {{ number_format($totalExpense,0,',','.') }}
            </td>
            <td class="blue">
                Difference<br>
                Rp {{ number_format($selisih,0,',','.') }}
            </td>
        </tr>
    </table>

    {{-- TRANSACTION TABLE --}}
    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>Category</th>
                <th>Description</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dailyRows as $row)
                <tr>
                    <td>{{ $row->date->format('H:i') }}</td>
                    <td>{{ $row->category?->name ?? '-' }}</td>
                    <td>{{ $row->title }}</td>
                    <td class="right {{ $row->type }}">
                        Rp {{ number_format($row->amount,0,',','.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
