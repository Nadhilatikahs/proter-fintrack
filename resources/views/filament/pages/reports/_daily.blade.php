{{-- resources/views/filament/pages/reports/_daily.blade.php --}}
<div
    x-data="dailyChart(
        {{ json_encode($daily['labels']) }},
        {{ json_encode($daily['income']) }},
        {{ json_encode($daily['expense']) }},
        {{ json_encode($daily['net']) }}
    )"
    x-init="render()"
    class="space-y-6"
>
    <div class="p-4 bg-lime-200/80 rounded-3xl shadow-sm">
        <h2 class="mb-2 text-lg font-semibold text-gray-900">
            Daily transactions
        </h2>
        <p class="mb-4 text-sm text-gray-700">
            Income, expense, dan saldo bersih per hari untuk range tanggal yang dipilih.
        </p>

        <div class="h-80">
            <canvas x-ref="canvas"></canvas>
        </div>
    </div>

    {{-- Tabel ringkas --}}
    <div class="p-4 bg-lime-200/80 rounded-3xl shadow-sm">
        <h3 class="mb-2 text-sm font-semibold text-gray-900">Ringkasan harian</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead>
                <tr class="text-left border-b border-lime-300/60">
                    <th class="py-2 pr-4">Tanggal</th>
                    <th class="py-2 pr-4 text-emerald-700">Income</th>
                    <th class="py-2 pr-4 text-red-600">Expense</th>
                    <th class="py-2 pr-4">Net</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($daily['labels'] as $idx => $date)
                    @php
                        $income  = $daily['income'][$idx]  ?? 0;
                        $expense = $daily['expense'][$idx] ?? 0;
                        $net     = $daily['net'][$idx]     ?? 0;
                    @endphp
                    <tr class="border-b border-lime-100/80">
                        <td class="py-1 pr-4">{{ $date }}</td>
                        <td class="py-1 pr-4 text-emerald-700">
                            Rp {{ number_format($income, 0, '.', ',') }}
                        </td>
                        <td class="py-1 pr-4 text-red-600">
                            Rp {{ number_format($expense, 0, '.', ',') }}
                        </td>
                        <td class="py-1 pr-4 {{ $net >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                            Rp {{ number_format($net, 0, '.', ',') }}
                        </td>
                    </tr>
                @endforeach
                @if (empty($daily['labels']))
                    <tr>
                        <td colspan="4" class="py-3 text-center text-gray-600">
                            Belum ada transaksi di range tanggal ini.
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
