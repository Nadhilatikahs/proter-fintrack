@extends('layouts.fintrack')


@section('top-left')
    <div class="ft-heading">Dashboard</div>
    <div class="ft-subheading">Welcome back, {{ auth()->user()->name }}</div>
@endsection

@section('content')
    <div class="ft-dashboard-grid">
        {{-- CASHFLOW OVERVIEW (line chart) --}}
        <section class="ft-card" style="grid-column: 1 / span 1;">
            <div class="ft-card-header">
                <h2 class="ft-card-title">Cashflow overview</h2>
                <span class="ft-chip">Saving, Lifestyle &amp; Food</span>
            </div>

            {{-- filter mode --}}
            <form method="GET" class="ft-filter-row">
                <div class="ft-segmented">
                    <button name="mode" value="day"
                            class="ft-seg-btn {{ $mode === 'day' ? 'is-active' : '' }}">Per tanggal</button>
                    <button name="mode" value="month"
                            class="ft-seg-btn {{ $mode === 'month' ? 'is-active' : '' }}">Per bulan</button>
                    <button name="mode" value="year"
                            class="ft-seg-btn {{ $mode === 'year' ? 'is-active' : '' }}">Per tahun</button>
                </div>

                <div class="ft-date-range">
                    @if($mode === 'year')
                        <input type="number" name="from" class="ft-input" placeholder="From year"
                               value="{{ $from }}">
                        <span class="ft-date-sep">–</span>
                        <input type="number" name="to" class="ft-input" placeholder="To year"
                               value="{{ $to }}">
                    @else
                        <input type="date" name="from" class="ft-input"
                               value="{{ $from }}">
                        <span class="ft-date-sep">–</span>
                        <input type="date" name="to" class="ft-input"
                               value="{{ $to }}">
                    @endif
                    <button class="ft-btn ft-btn-small" type="submit">Apply</button>
                </div>
            </form>

            <canvas id="cashflowChart" height="110"></canvas>
        </section>

        {{-- CALENDAR & SUMMARY BULAN INI --}}
        <section class="ft-card" style="grid-column: 2 / span 1;">
            <div class="ft-card-header">
                <h2 class="ft-card-title">{{ now()->format('M Y') }}</h2>
            </div>

            {{-- calendar simple --}}
            <div class="ft-calendar">
                @php
                    $calendarStart = now()->startOfMonth()->startOfWeek();
                    $calendarEnd   = now()->endOfMonth()->endOfWeek();
                @endphp

                <div class="ft-calendar-row ft-calendar-head">
                    @foreach(['M','T','W','T','F','S','S'] as $d)
                        <div>{{ $d }}</div>
                    @endforeach
                </div>

                @for($date = $calendarStart->copy(); $date <= $calendarEnd; $date->addDay())
                    @if($date->dayOfWeekIso === 1)
                        <div class="ft-calendar-row">
                    @endif

                    <div class="ft-calendar-cell {{ $date->month !== now()->month ? 'is-muted' : '' }}
                                {{ $date->isToday() ? 'is-today' : '' }}">
                        {{ $date->day }}
                    </div>

                    @if($date->dayOfWeekIso === 7)
                        </div>
                    @endif
                @endfor
            </div>
        </section>

        {{-- RINGKASAN BULAN INI (3 baris) --}}
        <section class="ft-card" style="grid-column: 3 / span 1;">
            <div class="ft-card-header">
                <h2 class="ft-card-title">Summary this month</h2>
            </div>

            <div class="ft-summary-list">
                <div class="ft-summary-item">
                    <span>Pemasukan bulan ini</span>
                    <strong class="ft-text-income">Rp {{ number_format($summary['income'],0,'.',',') }}</strong>
                </div>
                <div class="ft-summary-item">
                    <span>Pengeluaran bulan ini</span>
                    <strong class="ft-text-expense">Rp {{ number_format($summary['expense'],0,'.',',') }}</strong>
                </div>
                <div class="ft-summary-item">
                    <span>Saldo bulan ini</span>
                    <strong class="{{ $summary['balance'] >= 0 ? 'ft-text-income' : 'ft-text-expense' }}">
                        Rp {{ number_format($summary['balance'],0,'.',',') }}
                    </strong>
                </div>
            </div>

            <a href="{{ route('transactions.create') }}" class="ft-btn ft-btn-outline w-full" style="margin-top: 24px;">
                + Input Transaction
            </a>
        </section>

        {{-- LAST TRANSACTIONS --}}
        <section class="ft-card" style="grid-column: 1 / span 2;">
            <div class="ft-card-header">
                <h2 class="ft-card-title">Transactions</h2>
            </div>

            @if($lastTransactions->isEmpty())
                <p>Belum ada transaksi. Yuk mulai catat transaksi pertamamu ✨</p>
            @else
                <ul class="ft-tx-list">
                    @foreach($lastTransactions as $tx)
                        <li class="ft-tx-row">
                            <div>
                                <div class="ft-tx-name">{{ $tx->description ?? $tx->code ?? 'Transaksi' }}</div>
                                <div class="ft-tx-meta">
                                    {{ optional($tx->category)->name ?? '-' }} •
                                    {{ $tx->date->format('d M Y') }}
                                </div>
                            </div>
                            <div class="ft-tx-amount {{ $tx->type === 'income' ? 'ft-text-income' : 'ft-text-expense' }}">
                                {{ $tx->type === 'income' ? '+' : '-' }}
                                Rp {{ number_format($tx->amount,0,',','.') }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- PIE SPENDING BY CATEGORY --}}
        <section class="ft-card" style="grid-column: 3 / span 1;">
            <div class="ft-card-header">
                <h2 class="ft-card-title">Spending by category</h2>
            </div>
            <canvas id="categoryPieChart" height="180"></canvas>
        </section>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // data dari controller
    const cashflowLabels  = @json($cashflowLabels);
    const cashflowIncome  = @json($cashflowIncome);
    const cashflowExpense = @json($cashflowExpense);
    const pieLabels       = @json($pieLabels);
    const pieData         = @json($pieData);

    // line chart cashflow
    new Chart(document.getElementById('cashflowChart'), {
        type: 'line',
        data: {
            labels: cashflowLabels,
            datasets: [
                {
                    label: 'Income',
                    data: cashflowIncome,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34,197,94,0.15)',
                    tension: 0.35,
                    fill: true
                },
                {
                    label: 'Expense',
                    data: cashflowExpense,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,0.15)',
                    tension: 0.35,
                    fill: true
                }
            ]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            },
            plugins: {
                legend: { display: true }
            }
        }
    });

    // pie chart spending by category
    new Chart(document.getElementById('categoryPieChart'), {
        type: 'doughnut',
        data: {
            labels: pieLabels,
            datasets: [{
                data: pieData,
                backgroundColor: ['#8979ff', '#3cc3df', '#ff928a', '#22c55e', '#facc15', '#f97316']
            }]
        },
        options: {
            plugins: {
                legend: { position: 'bottom' }
            },
            cutout: '60%'
        }
    });
</script>
@endpush
