<x-filament::page>
<div x-data="{ tab: 'daily' }" class="space-y-6">

    {{-- ================= HEADER ================= --}}
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-black">This is your reports</h1>

        {{-- ================= HEADER ================= --}}
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-black"></h1>

            {{-- EXPORT DROPDOWN (FINAL) --}}
            <div x-data="{ open: false }" class="relative inline-block">

                {{-- BUTTON --}}
                <button
                    @click="open = !open"
                    class="fin-btn-dark flex items-center gap-2"
                >
                    Export
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- DROPDOWN --}}
                <div
                    x-show="open"
                    @click.outside="open = false"
                    x-transition
                    class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border z-50"
                >
                    <a
                        href="{{ route('filament.admin.pages.reports.export-cashflow', request()->query()) }}"
                        class="block px-4 py-2 text-sm font-semibold text-blue-800 hover:bg-gray-100 rounded-t-xl"
                    >
                        📈 Cash Flow PDF
                    </a>

                    <a
                        href="{{ route('filament.admin.pages.reports.export-budget-goal', request()->query()) }}"
                        class="block px-4 py-2 text-sm font-semibold text-green-800 hover:bg-gray-100"
                    >
                        📊 Budget / Goal PDF
                    </a>

                    <a
                        href="{{ route('filament.admin.pages.reports.export-daily', request()->query()) }}"
                        class="block px-4 py-2 text-sm font-semibold text-yellow-800 hover:bg-gray-100 rounded-b-xl"
                    >
                        📅 Daily PDF
                    </a>
                </div>
            </div>
        </div>

    </div>

    {{-- ================= FILTER RANGE (CASHFLOW) ================= --}}
    <form method="GET" class="flex items-center gap-3">
        <input type="date" name="from"
            value="{{ request('from', $from->format('Y-m-d')) }}"
            class="ft-input-date date-black-icon">

        <span class="text-gray-500">to</span>

        <input type="date" name="to"
            value="{{ request('to', $to->format('Y-m-d')) }}"
            class="ft-input-date date-black-icon">

        <button class="fin-btn-dark">Apply</button>
    </form>

    {{-- ================= TABS ================= --}}
    <div class="flex gap-6 text-sm font-semibold border-b">
        @foreach (['cashflow' => 'Cashflow', 'budget-goal' => 'Budget / Goal', 'daily' => 'Daily'] as $key => $label)
            <button @click="tab='{{ $key }}'"
                :class="tab==='{{ $key }}'
                    ? 'border-b-2 border-green-600 text-green-600'
                    : 'text-gray-400'">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ================= CASHFLOW ================= --}}
    <div x-show="tab==='cashflow'" x-transition class="space-y-6">

        {{-- SUMMARY --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- INCOME --}}
            <div class="bg-green-100 border border-green-300 rounded-xl p-4">
                <div class="text-sm text-green-700">Total Pemasukan</div>
                <div class="text-xl font-bold text-green-800">
                    Rp {{ number_format($totalIncome,0,',','.') }}
                </div>
            </div>

            {{-- EXPENSE --}}
            <div class="bg-red-100 border border-red-300 rounded-xl p-4">
                <div class="text-sm text-red-700">Total Pengeluaran</div>
                <div class="text-xl font-bold text-red-800">
                    Rp {{ number_format($totalExpense,0,',','.') }}
                </div>
            </div>

            {{-- SELISIH --}}
            <div class="bg-blue-100 border border-blue-300 rounded-xl p-4">
                <div class="text-sm text-blue-700">Selisih</div>
                <div class="text-xl font-bold {{ $selisih < 0 ? 'text-red-700' : 'text-blue-800' }}">
                    Rp {{ number_format($selisih,0,',','.') }}
                </div>
            </div>

        </div>

        {{-- CHART --}}
        <div class="bg-white rounded-xl p-6">
            <canvas id="cashflowChart" height="120"></canvas>
        </div>
    </div>

    {{-- ================= BUDGET / GOAL ================= --}}
    <div x-show="tab==='budget-goal'" x-transition
        class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT : PROGRESS + HISTORY --}}
        <div class="lg:col-span-2 bg-white/60 rounded-xl p-6 space-y-8">

            {{-- ===== BUDGET PROGRESS ===== --}}
            <div>
                <h3 class="font-semibold text-black mb-4">Budget</h3>

                @foreach($budgets as $b)
                    <div class="mb-6">
                        {{-- PROGRESS BAR --}}
                        @include('partials.progress-bar', [
                            'name' => $b->name,
                            'percent' => $b->percent,
                            'type' => 'budget'
                        ])

                        {{-- HISTORY PER KATEGORI --}}
                        <div class="mt-3 text-sm text-gray-700">
                            <div class="grid grid-cols-4 gap-4 text-xs font-semibold border-b pb-1">
                                <div>Kategori</div>
                                <div>Anggaran</div>
                                <div>Terpakai</div>
                                <div>Sisa</div>
                            </div>

                            <div class="grid grid-cols-4 gap-4 py-1">
                                <div>{{ $b->name }}</div>
                                <div>Rp {{ number_format($b->amount,0,',','.') }}</div>
                                <div class="text-red-600">
                                    Rp {{ number_format($b->spent,0,',','.') }}
                                </div>
                                <div class="text-green-700">
                                    Rp {{ number_format($b->remain,0,',','.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ===== GOAL PROGRESS ===== --}}
            <div>
                <h3 class="font-semibold text-black mb-4">Goal</h3>

                @foreach($goals as $g)
                    <div class="mb-6">
                        @include('partials.progress-bar', [
                            'name' => $g->name,
                            'percent' => $g->percent,
                            'type' => 'goal'
                        ])

                        <div class="mt-3 text-sm text-gray-700">
                            <div class="grid grid-cols-3 gap-4 text-xs font-semibold border-b pb-1">
                                <div>Target</div>
                                <div>Terkumpul</div>
                                <div>Sisa</div>
                            </div>

                            <div class="grid grid-cols-3 gap-4 py-1">
                                <div>Rp {{ number_format($g->target,0,',','.') }}</div>
                                <div class="text-green-700">
                                    Rp {{ number_format($g->saved,0,',','.') }}
                                </div>
                                <div>
                                    Rp {{ number_format(max($g->target - $g->saved,0),0,',','.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- RIGHT : SUMMARY WIDGET --}}
        <div class="space-y-4">

            {{-- ================= BUDGET SUMMARY ================= --}}
            @php
                $budgetExceeded = $totalSpent > $totalBudget;
                $exceedPercent  = $totalBudget > 0
                    ? round((($totalSpent - $totalBudget) / $totalBudget) * 100, 1)
                    : 0;
            @endphp

            <div
                class="rounded-xl p-5 border
                    {{ $budgetExceeded
                        ? 'bg-red-100 border-red-300'
                        : 'bg-green-100 border-green-300' }}"
            >
                <div class="text-sm font-semibold
                    {{ $budgetExceeded ? 'text-red-700' : 'text-green-700' }}">
                    Total Budget
                </div>

                <div class="text-xl font-bold
                    {{ $budgetExceeded ? 'text-red-800' : 'text-green-800' }}">
                    Rp {{ number_format($totalBudget,0,',','.') }}
                </div>

                <div class="mt-2 text-sm
                    {{ $budgetExceeded ? 'text-red-700' : 'text-green-700' }}">
                    @if($budgetExceeded)
                        ⚠️ Anda melebihi {{ $exceedPercent }}% dari target budget
                    @else
                        ✅ Anggaran masih aman
                    @endif
                </div>
            </div>

            {{-- ================= GOAL SUMMARY ================= --}}
            @foreach($goals as $g)
                <div class="bg-blue-100 border border-blue-300 rounded-xl p-5">
                    <div class="text-sm font-semibold text-blue-700">
                        Goal: {{ $g->name }}
                    </div>

                    <div class="text-lg font-bold text-blue-800">
                        {{ $g->percent }}% tercapai
                    </div>

                    <div class="mt-1 text-sm text-blue-700">
                        @if($g->saved >= $g->target)
                            🎉 Goal tercapai
                        @else
                            Sisa Rp {{ number_format($g->target - $g->saved,0,',','.') }} lagi
                        @endif
                    </div>
                </div>
            @endforeach

        </div>
    </div>


    {{-- ================= DAILY ================= --}}
    <div x-show="tab==='daily'" x-transition class="space-y-6">

        {{-- SUMMARY WIDGET (DAILY) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- TOTAL --}}
            <div class="bg-green-200 border border-green-400 rounded-xl p-4">
                <div class="text-sm text-green-800">Total</div>
                <div class="text-xl font-bold text-green-900">
                    Rp {{ number_format($dailyIncome,0,',','.') }}
                </div>
            </div>

            {{-- PENGELUARAN --}}
            <div class="bg-red-200 border border-red-400 rounded-xl p-4">
                <div class="text-sm text-red-800">Pengeluaran</div>
                <div class="text-xl font-bold text-red-900">
                    Rp {{ number_format($dailyExpense,0,',','.') }}
                </div>
            </div>

            {{-- SELISIH --}}
            <div class="bg-blue-200 border border-blue-400 rounded-xl p-4">
                <div class="text-sm text-blue-800">Selisih</div>
                <div class="text-xl font-bold {{ $dailySelisih < 0 ? 'text-red-700' : 'text-blue-900' }}">
                    Rp {{ number_format($dailySelisih,0,',','.') }}
                </div>
            </div>

        </div>


        {{-- DATE PICKER --}}
        <form method="GET" class="mt-2">
            <input
                type="date"
                name="date"
                value="{{ $date->format('Y-m-d') }}"
                class="ft-input-date date-black-icon"
                onchange="this.form.submit()"
            >
        </form>

        {{-- TABLE --}}
        <div class="bg-white rounded-xl p-4">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Deskripsi</th>
                        <th class="text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dailyRows as $row)
                        <tr class="border-b">
                            <td>{{ $row->date->format('Y-m-d') }}</td>
                            <td>{{ $row->category?->name ?? '-' }}</td>
                            <td>{{ $row->title }}</td>
                            <td class="text-right {{ $row->type === 'expense' ? 'text-red-600' : 'text-green-700' }}">
                                Rp {{ number_format($row->amount,0,',','.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>


{{-- ================= CHART SCRIPT ================= --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('cashflowChart');
    if (!el) return;

    new Chart(el, {
        type: 'line',
        data: {
            labels: @json($labels),
            datasets: [
                {
                    label: 'Pemasukan',
                    data: @json($income),
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34,197,94,.15)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Pengeluaran',
                    data: @json($expense),
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,.15)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
});
</script>

<style>
.date-black-icon::-webkit-calendar-picker-indicator {
    filter: invert(0);
    opacity: 1;
}
</style>
</x-filament::page>
