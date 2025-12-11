<x-filament-panels::page>
    <div class="ft-reports-layout">
        {{-- HEADER --}}
        <header class="ft-report-header">
            <div>
                <h1 class="ft-page-title">Reports</h1>
                <p class="ft-page-subtitle">
                    Analisis cash flow, budget, transaksi harian dan goals kamu.
                </p>
            </div>

            {{-- Filter tanggal global --}}
            <div class="ft-report-daterange">
                <label class="ft-label">
                    From
                    <input
                        type="date"
                        wire:model.live="fromDate"
                        class="ft-input-date"
                    >
                </label>
                <label class="ft-label">
                    To
                    <input
                        type="date"
                        wire:model.live="toDate"
                        class="ft-input-date"
                    >
                </label>
            </div>
        </header>

        {{-- TABS --}}
        <nav class="ft-report-tabs">
            <button
                type="button"
                class="ft-tab-btn {{ $tab === 'cash-flow' ? 'is-active' : '' }}"
                wire:click="$set('tab','cash-flow')"
            >
                Cash Flow
            </button>
            <button
                type="button"
                class="ft-tab-btn {{ $tab === 'budget' ? 'is-active' : '' }}"
                wire:click="$set('tab','budget')"
            >
                Budget
            </button>
            <button
                type="button"
                class="ft-tab-btn {{ $tab === 'daily' ? 'is-active' : '' }}"
                wire:click="$set('tab','daily')"
            >
                Daily
            </button>
            <button
                type="button"
                class="ft-tab-btn {{ $tab === 'goal' ? 'is-active' : '' }}"
                wire:click="$set('tab','goal')"
            >
                Goals
            </button>
        </nav>

        {{-- CASH FLOW TAB --}}
        @if($tab === 'cash-flow')
            <section class="ft-report-section">
                <div class="ft-card ft-card-light">
                    <h2 class="ft-section-title">Cash Flow Overview</h2>
                    <p class="ft-section-subtitle">
                        Pergerakan pemasukan dan pengeluaran berdasarkan rentang tanggal yang kamu pilih.
                    </p>

                    <div class="ft-report-chart-wrapper">
                        <canvas id="cashFlowChart"></canvas>
                    </div>

                    <div class="ft-report-summary-grid">
                        <div class="ft-summary-pill ft-summary-income">
                            <span>Total Income</span>
                            <strong>Rp {{ number_format($cashFlow['total']['income'], 0, ',', '.') }}</strong>
                        </div>
                        <div class="ft-summary-pill ft-summary-expense">
                            <span>Total Expense</span>
                            <strong>Rp {{ number_format($cashFlow['total']['expense'], 0, ',', '.') }}</strong>
                        </div>
                        <div class="ft-summary-pill ft-summary-balance">
                            <span>Net Cash Flow</span>
                            <strong>Rp {{ number_format($cashFlow['total']['diff'], 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- BUDGET TAB --}}
        @if($tab === 'budget')
            <section class="ft-report-section">
                <div class="ft-card ft-card-light">
                    <h2 class="ft-section-title">Budget Utilization</h2>
                    <p class="ft-section-subtitle">
                        Pemakaian budget per kategori dan ringkasan total anggaran.
                    </p>

                    <div class="ft-report-chart-wrapper">
                        <canvas id="budgetChart"></canvas>
                    </div>

                    <div class="ft-report-summary-grid">
                        <div class="ft-summary-pill ft-summary-income">
                            <span>Total Budget</span>
                            <strong>
                                Rp {{ number_format($budget['summary']['total_limit'] ?? 0, 0, ',', '.') }}
                            </strong>
                        </div>
                        <div class="ft-summary-pill ft-summary-expense">
                            <span>Total Spent</span>
                            <strong>
                                Rp {{ number_format($budget['summary']['total_used'] ?? 0, 0, ',', '.') }}
                            </strong>
                        </div>
                        <div class="ft-summary-pill ft-summary-balance">
                            <span>Remaining Budget</span>
                            <strong>
                                Rp {{ number_format($budget['summary']['remaining'] ?? 0, 0, ',', '.') }}
                            </strong>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- DAILY TAB --}}
        @if($tab === 'daily')
            <section class="ft-report-section">
                <div class="ft-card ft-card-light">
                    <h2 class="ft-section-title">Daily Transactions</h2>
                    <p class="ft-section-subtitle">
                        Ringkasan pemasukan, pengeluaran dan net per hari.
                    </p>

                    <div class="ft-report-chart-wrapper">
                        <canvas id="dailyChart"></canvas>
                    </div>

                    <div class="ft-table-wrapper">
                        <table class="ft-report-table">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Income</th>
                                <th>Expense</th>
                                <th>Net</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($daily['labels'] as $idx => $date)
                                <tr>
                                    <td>{{ $date }}</td>
                                    <td>Rp {{ number_format($daily['income'][$idx] ?? 0, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($daily['expense'][$idx] ?? 0, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($daily['net'][$idx] ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="ft-empty-cell">
                                        Belum ada data pada rentang tanggal ini.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        {{-- GOAL TAB --}}
        @if($tab === 'goal')
            <section class="ft-report-section">
                <div class="ft-card ft-card-light">
                    <h2 class="ft-section-title">Financial Goals</h2>
                    <p class="ft-section-subtitle">
                        Perbandingan antara goal yang sudah tercapai dan yang masih berjalan.
                    </p>

                    <div class="ft-report-chart-wrapper">
                        <canvas id="goalChart"></canvas>
                    </div>

                    <div class="ft-report-summary-grid">
                        <div class="ft-summary-pill ft-summary-income">
                            <span>Goals Achieved</span>
                            <strong>{{ $goals['done'] }}</strong>
                        </div>
                        <div class="ft-summary-pill ft-summary-expense">
                            <span>Goals In Progress</span>
                            <strong>{{ $goals['running'] }}</strong>
                        </div>
                        <div class="ft-summary-pill ft-summary-balance">
                            <span>Total Goals</span>
                            <strong>{{ $goals['total'] }}</strong>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>

    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('livewire:navigated', renderReportsCharts);
        document.addEventListener('DOMContentLoaded', renderReportsCharts);

        function renderReportsCharts() {
            const tab = @json($tab);

            const cashFlow = @json($cashFlow);
            const budget   = @json($budget);
            const daily    = @json($daily);
            const goals    = @json($goals);

            // CASH FLOW CHART
            if (tab === 'cash-flow') {
                const el = document.getElementById('cashFlowChart');
                if (el) {
                    if (el._chartInstance) {
                        el._chartInstance.destroy();
                    }
                    el._chartInstance = new Chart(el, {
                        type: 'line',
                        data: {
                            labels: cashFlow.labels ?? [],
                            datasets: [
                                {
                                    label: 'Income',
                                    data: cashFlow.income ?? [],
                                },
                                {
                                    label: 'Expense',
                                    data: cashFlow.expense ?? [],
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                },
                            },
                        },
                    });
                }
            }

            // BUDGET CHART (horizontal bar)
            if (tab === 'budget') {
                const el = document.getElementById('budgetChart');
                if (el) {
                    if (el._chartInstance) {
                        el._chartInstance.destroy();
                    }
                    el._chartInstance = new Chart(el, {
                        type: 'bar',
                        data: {
                            labels: budget.labels ?? [],
                            datasets: [
                                {
                                    label: 'Limit',
                                    data: budget.limit ?? [],
                                },
                                {
                                    label: 'Used',
                                    data: budget.used ?? [],
                                },
                            ],
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                },
                            },
                        },
                    });
                }
            }

            // DAILY CHART (income vs expense)
            if (tab === 'daily') {
                const el = document.getElementById('dailyChart');
                if (el) {
                    if (el._chartInstance) {
                        el._chartInstance.destroy();
                    }
                    el._chartInstance = new Chart(el, {
                        type: 'bar',
                        data: {
                            labels: daily.labels ?? [],
                            datasets: [
                                {
                                    label: 'Income',
                                    data: daily.income ?? [],
                                },
                                {
                                    label: 'Expense',
                                    data: daily.expense ?? [],
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                },
                            },
                        },
                    });
                }
            }

            // GOALS CHART (donut)
            if (tab === 'goal') {
                const el = document.getElementById('goalChart');
                if (el) {
                    if (el._chartInstance) {
                        el._chartInstance.destroy();
                    }
                    el._chartInstance = new Chart(el, {
                        type: 'doughnut',
                        data: {
                            labels: ['Achieved', 'In Progress'],
                            datasets: [
                                {
                                    data: [
                                        goals.done ?? 0,
                                        goals.running ?? 0,
                                    ],
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                },
                            },
                        },
                    });
                }
            }
        }
    </script>
</x-filament-panels::page>
