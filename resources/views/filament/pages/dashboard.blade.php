<x-filament::page>
        {{-- TITLE + WELCOME --}}
            <div class="ft-page-subtitle">
                Welcome back, {{ $userName }}
            </div>

        {{-- SUMMARY ROW --}}
        <div class="ft-summary-row">
            <div class="ft-summary-card ft-summary-income">
                <div class="ft-summary-title">Income this period</div>
                <div class="ft-summary-value">
                    Rp {{ number_format($summary['income'] ?? 0, 0, '.', ',') }}
                </div>
            </div>

            <div class="ft-summary-card ft-summary-expense">
                <div class="ft-summary-title">Expense this period</div>
                <div class="ft-summary-value">
                    Rp {{ number_format($summary['expense'] ?? 0, 0, '.', ',') }}
                </div>
            </div>

            <div class="ft-summary-card ft-summary-balance">
                <div class="ft-summary-title">Balance</div>
                <div class="ft-summary-value">
                    Rp {{ number_format($summary['balance'] ?? 0, 0, '.', ',') }}
                </div>
            </div>
        </div>

        {{-- DATE FILTER + ADD TRANSACTION BUTTON --}}
        <div class="ft-dashboard-toolbar">
            <div class="ft-date-range">
                <span class="ft-label">Period</span>
                <input
                    type="date"
                    wire:model.live="fromDate"
                    class="ft-input-date"
                />
                <span class="ft-label">to</span>
                <input
                    type="date"
                    wire:model.live="toDate"
                    class="ft-input-date"
                />
            </div>

            <a
                href="{{ \App\Filament\Resources\TransactionResource::getUrl('create') }}"
                class="ft-btn-outline-green"
            >
                + Add Your Transaction Today
            </a>
        </div>

        {{-- CHARTS --}}
        <div class="ft-dashboard-grid">
            {{-- PIE: SPENDING BY CATEGORY --}}
            <div class="ft-card ft-card-light">
                <div class="ft-section-title">Spending by category</div>
                <canvas id="category-chart" height="220"></canvas>
            </div>

            {{-- BAR: CASHFLOW BY DAY --}}
            <div class="ft-card ft-card-light">
                <div class="ft-section-title">Cashflow by day</div>
                <canvas id="daily-chart" height="220"></canvas>
            </div>
        </div>

        {{-- LAST TRANSACTIONS --}}
        <div class="ft-card ft-card-light ft-last-transactions">
            <div class="ft-last-header">
                <div class="ft-detail-title">
                    Detail Transaction
                    <span class="ft-arrow-up-right">↗</span>
                </div>
                <a
                    href="{{ \App\Filament\Resources\TransactionResource::getUrl('index') }}"
                    class="text-sm underline"
                    style="color: var(--ft-navy);"
                >
                    View all
                </a>
            </div>

            @if ($lastTransactions->isEmpty())
                <p class="ft-empty-state">
                    No transactions yet for this period.
                </p>
            @else
                <ul class="ft-transaction-list">
                    @foreach ($lastTransactions as $tx)
                        <li class="ft-transaction-item">
                            <div class="ft-transaction-main">
                                <div class="ft-transaction-title">
                                    {{ $tx->description ?? $tx->name ?? 'Transaction' }}
                                </div>
                                <div class="ft-transaction-meta">
                                    {{ \Illuminate\Support\Carbon::parse($tx->date)->format('d F Y') }}
                                    @if ($tx->category)
                                        • {{ $tx->category->name }}
                                    @endif
                                    • {{ ucfirst($tx->type) }}
                                </div>
                            </div>

                            <div class="ft-transaction-amount {{ $tx->type === 'expense' ? 'is-expense' : 'is-income' }}">
                                {{ $tx->type === 'expense' ? '-' : '+' }}
                                Rp {{ number_format($tx->amount, 0, '.', ',') }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

    {{-- Chart.js --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('livewire:navigated', renderCharts);
            document.addEventListener('DOMContentLoaded', renderCharts);

            function renderCharts() {
                const catCtx = document.getElementById('category-chart');
                const dayCtx = document.getElementById('daily-chart');

                if (!catCtx || !dayCtx) return;

                // destroy existing instance if re-render
                if (catCtx.__chart) {
                    catCtx.__chart.destroy();
                }
                if (dayCtx.__chart) {
                    dayCtx.__chart.destroy();
                }

                // data dari backend
                const categoryLabels = @json($categoryChart['labels']);
                const categoryData   = @json($categoryChart['data']);

                const dailyLabels  = @json($dailyChart['labels']);
                const dailyIncome  = @json($dailyChart['income']);
                const dailyExpense = @json($dailyChart['expense']);

                catCtx.__chart = new Chart(catCtx, {
                    type: 'pie',
                    data: {
                        labels: categoryLabels,
                        datasets: [{
                            data: categoryData,
                            borderWidth: 1,
                        }],
                    },
                    options: {
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 14,
                                    color: '#1F2937',
                                    font: { family: 'Radio Canada Big' },
                                },
                            },
                        },
                    },
                });

                dayCtx.__chart = new Chart(dayCtx, {
                    type: 'bar',
                    data: {
                        labels: dailyLabels,
                        datasets: [
                            {
                                label: 'Income',
                                data: dailyIncome,
                                borderWidth: 1,
                            },
                            {
                                label: 'Expense',
                                data: dailyExpense,
                                borderWidth: 1,
                            },
                        ],
                    },
                    options: {
                        scales: {
                            x: {
                                ticks: {
                                    color: '#1F2937',
                                    font: { family: 'Radio Canada Big' },
                                },
                            },
                            y: {
                                ticks: {
                                    color: '#1F2937',
                                    font: { family: 'Radio Canada Big' },
                                },
                            },
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: '#1F2937',
                                    font: { family: 'Radio Canada Big' },
                                },
                            },
                        },
                    },
                });
            }
        </script>
    @endpush
</x-filament::page>
