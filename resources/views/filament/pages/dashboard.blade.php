<x-filament::page>
    <div class="space-y-6" wire:poll.30s>
        {{-- Welcome Message --}}
        <div>
            <h2 class="text-2xl font-bold">Welcome back, {{ $userName }}</h2>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6" style="background: #EFF6D2;">
                <p class="text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Income this period</p>
                <p class="text-2xl font-bold text-green-600">
                    Rp {{ number_format($summary['income'] ?? 0, 0, ',', '.') }}
                </p>
            </div>
            <div class="bg-white rounded-lg shadow p-6" style="background: #FFE5E5;">
                <p class="text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Expense this period</p>
                <p class="text-2xl font-bold text-red-600">
                    Rp {{ number_format($summary['expense'] ?? 0, 0, ',', '.') }}
                </p>
            </div>
            <div class="bg-white rounded-lg shadow p-6" style="background: #E5F3FF;">
                <p class="text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Balance</p>
                <p class="text-2xl font-bold" style="color: #1F2937;">
                    Rp {{ number_format($summary['balance'] ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Date Filter and Add Transaction Button --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-sm font-medium text-gray-700">Period</span>
                <input 
                    type="date" 
                    wire:model.live="fromDate"
                    class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                    value="{{ $fromDate ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}"
                />
                <span class="text-sm text-gray-500">to</span>
                <input 
                    type="date" 
                    wire:model.live="toDate"
                    class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                    value="{{ $toDate ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}"
                />
            </div>
            <a 
                href="{{ route('filament.admin.resources.transactions.create') }}"
                class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors duration-200 whitespace-nowrap shadow-md"
                style="background: #7BAD3E;"
            >
                + Add Your Transaction Today
            </a>
        </div>

        {{-- Charts Row - Sejajar ke samping --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Category Pie Chart (Kiri) --}}
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Spending by category</h3>
                <div class="h-96 relative">
                    @if(count($categoryChart['labels'] ?? []) > 0)
                        <canvas id="categoryPie"></canvas>
                    @else
                        <div class="absolute inset-0 flex items-center justify-center">
                            <p class="text-gray-500 text-center">No spending data available</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Daily Cashflow Chart (Kanan) --}}
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Cashflow by day</h3>
                <div class="h-96 relative">
                    @if(count($dailyChart['labels'] ?? []) > 0)
                        <canvas id="dailyChart"></canvas>
                    @else
                        <div class="absolute inset-0 flex items-center justify-center">
                            <p class="text-gray-500 text-center">No cashflow data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Recent Transactions</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($lastTransactions as $tx)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $tx->title ?? $tx->name ?? $tx->description ?? 'Transaction' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($tx->date)->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $tx->category->name ?? 'Uncategorised' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $tx->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $tx->type === 'income' ? '+' : '-' }}
                                    Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No transactions found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Category Pie Chart
                const pieCtx = document.getElementById('categoryPie');
                @php
                    $categoryLabels = $categoryChart['labels'] ?? [];
                    $categoryData = $categoryChart['data'] ?? [];
                    $hasCategoryData = count($categoryLabels) > 0;
                @endphp
                if (pieCtx && @json($hasCategoryData)) {
                    const categoryLabels = @json($categoryLabels);
                    const categoryData = @json($categoryData);
                    const colors = ['#3B82F6', '#F59E0B', '#06B6D4', '#EF4444', '#8B5CF6', '#EC4899'];
                    
                    new Chart(pieCtx, {
                        type: 'pie',
                        data: {
                            labels: categoryLabels,
                            datasets: [{
                                data: categoryData,
                                backgroundColor: categoryLabels.map((_, i) => colors[i % colors.length]),
                                borderWidth: 2,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                        font: {
                                            size: 12
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                                            label += 'Rp ' + context.parsed.toLocaleString('id-ID') + ' (' + percentage + '%)';
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // Daily Cashflow Chart
                const dailyCtx = document.getElementById('dailyChart');
                @php
                    $dailyLabels = $dailyChart['labels'] ?? [];
                    $dailyIncome = $dailyChart['income'] ?? [];
                    $dailyExpense = $dailyChart['expense'] ?? [];
                    $hasDailyData = count($dailyLabels) > 0;
                @endphp
                if (dailyCtx && @json($hasDailyData)) {
                    const dailyLabels = @json($dailyLabels);
                    const dailyIncome = @json($dailyIncome);
                    const dailyExpense = @json($dailyExpense);
                    
                    // Format labels untuk display
                    const formattedLabels = dailyLabels.map(label => {
                        const date = new Date(label);
                        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    });
                    
                    new Chart(dailyCtx, {
                        type: 'bar',
                        data: {
                            labels: formattedLabels,
                            datasets: [
                                {
                                    label: 'Income',
                                    data: dailyIncome,
                                    backgroundColor: 'rgba(59, 130, 246, 0.6)',
                                    borderColor: '#3B82F6',
                                    borderWidth: 1,
                                    borderRadius: 4
                                },
                                {
                                    label: 'Expense',
                                    data: dailyExpense,
                                    backgroundColor: 'rgba(236, 72, 153, 0.6)',
                                    borderColor: '#EC4899',
                                    borderWidth: 1,
                                    borderRadius: 4
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                    align: 'center',
                                    labels: {
                                        padding: 20,
                                        usePointStyle: true,
                                        pointStyle: 'rectRounded',
                                        font: {
                                            size: 12
                                        },
                                        generateLabels: function(chart) {
                                            const original = Chart.defaults.plugins.legend.labels.generateLabels;
                                            const labels = original.call(this, chart);
                                            labels.forEach(label => {
                                                label.fillStyle = label.strokeStyle;
                                            });
                                            return labels;
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 11
                                        }
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.08)',
                                        drawBorder: false
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            if (value >= 1000000) {
                                                return (value / 1000000).toFixed(1) + 'M';
                                            } else if (value >= 1000) {
                                                return (value / 1000).toFixed(0) + 'K';
                                            }
                                            return value;
                                        },
                                        font: {
                                            size: 11
                                        },
                                        padding: 10
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
</x-filament::page>
