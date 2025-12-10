{{-- resources/views/filament/pages/reports/_cash_flow.blade.php --}}
<div
    x-data="cashFlowChart(
        {{ json_encode($cashFlow['labels']) }},
        {{ json_encode($cashFlow['income']) }},
        {{ json_encode($cashFlow['expense']) }}
    )"
    x-init="render()"
    class="grid gap-6 lg:grid-cols-3"
>
    {{-- Chart --}}
    <div class="p-4 bg-lime-200/80 rounded-3xl shadow-sm lg:col-span-2">
        <h2 class="mb-2 text-lg font-semibold text-gray-900">
            Cashflow overview
        </h2>
        <p class="mb-4 text-sm text-gray-700">
            Pergerakan pemasukan & pengeluaran berdasarkan tanggal dalam periode yang dipilih.
        </p>
        <div class="h-72">
            <canvas x-ref="canvas"></canvas>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="flex flex-col gap-4">
        <div class="p-4 bg-lime-200/80 rounded-3xl shadow-sm">
            <p class="text-xs font-medium text-gray-600 uppercase">Total income</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700">
                Rp {{ number_format($cashFlow['total']['income'] ?? 0, 0, '.', ',') }}
            </p>
        </div>

        <div class="p-4 bg-lime-200/80 rounded-3xl shadow-sm">
            <p class="text-xs font-medium text-gray-600 uppercase">Total expense</p>
            <p class="mt-1 text-2xl font-bold text-red-600">
                Rp {{ number_format($cashFlow['total']['expense'] ?? 0, 0, '.', ',') }}
            </p>
        </div>

        <div class="p-4 bg-lime-200/80 rounded-3xl shadow-sm">
            <p class="text-xs font-medium text-gray-600 uppercase">Difference</p>
            @php $diff = $cashFlow['total']['diff'] ?? 0; @endphp
            <p class="mt-1 text-2xl font-bold {{ $diff >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                Rp {{ number_format($diff, 0, '.', ',') }}
            </p>
            <p class="mt-1 text-xs text-gray-700">
                {{ $diff >= 0 ? 'Surplus' : 'Defisit' }} selama periode ini.
            </p>
        </div>
    </div>
</div>
