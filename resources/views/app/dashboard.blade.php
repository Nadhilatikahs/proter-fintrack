{{-- resources/views/app/dashboard.blade.php --}}
@extends('layouts.fintrack')



@section('content')
    <div class="ft-page-header">
        <div>
            <h1 class="ft-page-title">Dashboard</h1>
            <p class="ft-page-subtitle">
                Welcome back, {{ auth()->user()->name ?? 'James' }}
            </p>
        </div>

        <div class="ft-date-picker">
            <span class="ft-date-label">{{ $selectedMonthLabel }}</span>
            <button class="ft-date-next">&rsaquo;</button>
        </div>
    </div>

    {{-- ROW 1: Chart + Calendar + CTA --}}
    <div class="ft-row ft-row-top">
        {{-- Chart --}}
        <section class="ft-card ft-card-chart">
            <header class="ft-card-header">
                <div>
                    <h2>Cashflow overview</h2>
                    <p>Saving, Lifestyle &amp; Food</p>
                </div>
            </header>

            <div class="ft-chart-wrapper">
                <canvas id="cashflowChart" height="140"></canvas>
            </div>

            <div class="ft-chart-legend">
                <span class="ft-legend-item">
                    <span class="ft-legend-dot legend-saving"></span> Saving
                </span>
                <span class="ft-legend-item">
                    <span class="ft-legend-dot legend-lifestyle"></span> Lifestyle
                </span>
                <span class="ft-legend-item">
                    <span class="ft-legend-dot legend-food"></span> Food
                </span>
            </div>
        </section>

        {{-- Calendar --}}
        <section class="ft-card ft-card-calendar">
            <header class="ft-card-header ft-card-header--calendar">
                <h2>{{ $selectedMonthLabel }}</h2>
            </header>
            <div class="ft-calendar">
                <div class="ft-calendar-row ft-calendar-dow">
                    <span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span><span>S</span>
                </div>
                {{-- Simplified static calendar (bisa kamu ubah dinamis nanti) --}}
                @for ($r = 0; $r < 5; $r++)
                    <div class="ft-calendar-row">
                        @for ($c = 1; $c <= 7; $c++)
                            <span>{{ $r * 7 + $c }}</span>
                        @endfor
                    </div>
                @endfor
            </div>
        </section>

        {{-- CTA Input transaction --}}
        <section class="ft-card ft-card-cta">
            <button class="ft-btn-ghost"
                    onclick="window.location='{{ route('filament.admin.resources.transactions.create') ?? '#' }}'">
                + Input Transaction
            </button>

            <div class="ft-summary-row">
                <div class="ft-summary-item income">
                    <span class="label">Pemasukan bulan ini</span>
                    <span class="value">
                        Rp {{ number_format($summary['income'], 0, ',', '.') }}
                    </span>
                </div>
                <div class="ft-summary-item expense">
                    <span class="label">Pengeluaran bulan ini</span>
                    <span class="value">
                        Rp {{ number_format($summary['expense'], 0, ',', '.') }}
                    </span>
                </div>
                <div class="ft-summary-item balance">
                    <span class="label">Saldo bulan ini</span>
                    <span class="value">
                        Rp {{ number_format($summary['balance'], 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </section>
    </div>

    {{-- ROW 2: Transactions + Pie --}}
    <div class="ft-row ft-row-bottom">
        {{-- Transactions list --}}
        <section class="ft-card ft-card-transactions">
            <header class="ft-card-header">
                <h2>Transactions</h2>
            </header>

            <ul class="ft-tx-list">
                @forelse($latestTransactions as $tx)
                    <li class="ft-tx-item">
                        <div class="ft-tx-main">
                            <p class="ft-tx-name">{{ $tx->name }}</p>
                            <p class="ft-tx-date">
                                {{ \Carbon\Carbon::parse($tx->date)->format('F d, Y H:i') }}
                            </p>
                        </div>
                        <div class="ft-tx-amount {{ $tx->type === 'income' ? 'is-income' : 'is-expense' }}">
                            {{ $tx->type === 'income' ? '+' : '-' }}
                            Rp {{ number_format($tx->amount, 0, ',', '.') }}
                        </div>
                    </li>
                @empty
                    <li class="ft-tx-empty">
                        Belum ada transaksi. Yuk mulai catat transaksi pertamamu ✨
                    </li>
                @endforelse
            </ul>
        </section>

        {{-- Pie chart --}}
        <section class="ft-card ft-card-pie">
            <header class="ft-card-header">
                <h2>Spending by category</h2>
            </header>

            <div class="ft-pie-wrapper">
                <canvas id="categoryPie" height="160"></canvas>
            </div>

            <ul class="ft-pie-legend">
                @foreach($categoryPieData['labels'] as $idx => $label)
                    <li>
                        <span class="ft-legend-dot legend-{{ $idx }}"></span>
                        <span>{{ $label }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
@endsection

@push('scripts')
    {{-- Chart.js CDN, tanpa npm --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const cashflowCtx = document.getElementById('cashflowChart').getContext('2d');
        const cashflowData = @json($cashflowChartData);

        new Chart(cashflowCtx, {
            type: 'line',
            data: cashflowData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    x: {
                        ticks: { color: '#374151' },
                        grid: { display: false }
                    },
                    y: {
                        ticks: { color: '#374151' },
                        grid: { color: 'rgba(31,41,55,0.08)' }
                    }
                }
            }
        });

        const pieCtx = document.getElementById('categoryPie').getContext('2d');
        const pieData = @json($categoryPieData);

        new Chart(pieCtx, {
            type: 'doughnut',
            data: pieData,
            options: {
                cutout: '55%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>
@endpush
