@extends('layouts.fintrack')

@section('title','Reports')

@section('top-left')
    <div class="ft-heading">Reports</div>
    <div class="ft-subheading">Laporan keuangan lengkap ala Fintrack</div>
@endsection

@section('content')
    <div class="ft-tabs">
        <button class="ft-tab-btn is-active" data-tab="cashflow">Cash Flow</button>
        <button class="ft-tab-btn" data-tab="budget">Budget</button>
        <button class="ft-tab-btn" data-tab="daily">Daily</button>
        <button class="ft-tab-btn" data-tab="goal">Goal</button>
    </div>

    {{-- CASH FLOW --}}
    <section class="ft-tab-panel" id="tab-cashflow">
        <div class="ft-report-grid">
            <div class="ft-card">
                <div class="ft-card-header">
                    <h2 class="ft-card-title">Cash Flow</h2>
                </div>
                <canvas id="reportCashflowChart" height="140"></canvas>
            </div>

            <div class="ft-card ft-stat-col">
                <div class="ft-stat-card">
                    <div class="ft-stat-label">Total income</div>
                    <div class="ft-stat-value">Rp {{ number_format($cashflowTotals['income'],0,'.',',') }}</div>
                </div>
                <div class="ft-stat-card">
                    <div class="ft-stat-label">Total expense</div>
                    <div class="ft-stat-value">Rp {{ number_format($cashflowTotals['expense'],0,'.',',') }}</div>
                </div>
                <div class="ft-stat-card">
                    <div class="ft-stat-label">Difference</div>
                    <div class="ft-stat-value">
                        Rp {{ number_format($cashflowTotals['income'] - $cashflowTotals['expense'],0,'.',',') }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- BUDGET --}}
    <section class="ft-tab-panel is-hidden" id="tab-budget">
        <div class="ft-card">
            <div class="ft-card-header">
                <h2 class="ft-card-title">Budget usage by category</h2>
            </div>
            <canvas id="reportBudgetChart" height="160"></canvas>
        </div>
    </section>

    {{-- DAILY --}}
    <section class="ft-tab-panel is-hidden" id="tab-daily">
        <div class="ft-card">
            <div class="ft-card-header">
                <h2 class="ft-card-title">Daily summary</h2>
            </div>

            {{-- bisa ditambah filter tanggal --}}
            <table class="ft-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Income</th>
                        <th>Expense</th>
                        <th>Net</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dailyRows as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td class="ft-text-income">Rp {{ number_format($row['income'],0,'.',',') }}</td>
                            <td class="ft-text-expense">Rp {{ number_format($row['expense'],0,'.',',') }}</td>
                            <td>
                                Rp {{ number_format($row['income'] - $row['expense'],0,'.',',') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- GOAL --}}
    <section class="ft-tab-panel is-hidden" id="tab-goal">
        <div class="ft-card">
            <div class="ft-card-header">
                <h2 class="ft-card-title">Goals achievement</h2>
            </div>
            <p>Total goals: {{ $goalStats['total'] }}</p>
            <p>Achieved: {{ $goalStats['achieved'] }}</p>
            <p>Not yet: {{ $goalStats['total'] - $goalStats['achieved'] }}</p>

            <canvas id="reportGoalChart" height="120"></canvas>
        </div>
    </section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // tabs
    document.querySelectorAll('.ft-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;
            document.querySelectorAll('.ft-tab-btn').forEach(b => b.classList.remove('is-active'));
            document.querySelectorAll('.ft-tab-panel').forEach(p => p.classList.add('is-hidden'));
            btn.classList.add('is-active');
            document.getElementById('tab-' + tab).classList.remove('is-hidden');
        });
    });

    // data dari controller
    const rfLabels   = @json($reportCashflowLabels);
    const rfIncome   = @json($reportCashflowIncome);
    const rfExpense  = @json($reportCashflowExpense);

    const rbLabels   = @json($reportBudgetLabels);
    const rbUsage    = @json($reportBudgetUsage);

    const goalData   = @json($goalStats);

    new Chart(document.getElementById('reportCashflowChart'), {
        type: 'line',
        data: {
            labels: rfLabels,
            datasets: [
                { label:'Income', data: rfIncome,  borderColor:'#22c55e', tension:0.25 },
                { label:'Expense',data: rfExpense, borderColor:'#ef4444', tension:0.25 }
            ]
        }
    });

    new Chart(document.getElementById('reportBudgetChart'), {
        type: 'bar',
        data: {
            labels: rbLabels,
            datasets: [{
                label: 'Usage %',
                data: rbUsage,
                backgroundColor: '#4f46e5'
            }]
        },
        options: {
            indexAxis: 'y'
        }
    });

    new Chart(document.getElementById('reportGoalChart'), {
        type: 'doughnut',
        data: {
            labels: ['Achieved','Not yet'],
            datasets: [{
                data: [goalData.achieved, goalData.total - goalData.achieved],
                backgroundColor: ['#22c55e','#e5e7eb']
            }]
        }
    });
</script>
@endpush
