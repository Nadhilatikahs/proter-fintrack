@extends('layouts.fintrack')

@section('title','Budget & Goals')

@section('top-left')
    <div class="ft-heading">Budget &amp; Goals</div>
    <div class="ft-subheading">Manage your limits and dreams</div>
@endsection

@section('content')
    <div class="ft-page-header-row">
        <a href="{{ route('budget-goals.create') }}" class="ft-btn ft-btn-primary">
            + Add new Budget/Goals
        </a>
    </div>

    {{-- LIST BUDGET / GOALS --}}
    <div class="ft-budget-grid">
        @forelse($items as $item)
            @php
                $usage = $item->usage_percentage ?? 0; // kalau sudah dihitung di model
                if ($usage >= 90)      $barClass = 'is-green';
                elseif ($usage >= 50)  $barClass = 'is-yellow';
                else                   $barClass = 'is-red';
            @endphp

            <div class="ft-card ft-budget-card">
                <div class="ft-card-header">
                    <div>
                        <div class="ft-budget-name">{{ $item->name }}</div>
                        <div class="ft-budget-meta">
                            {{ ucfirst($item->type) }}
                            @if($item->category)
                                • {{ $item->category->name }}
                            @endif
                            • {{ ucfirst($item->period) }}
                        </div>
                    </div>
                    <div class="ft-budget-actions">
                        <a href="{{ route('budget-goals.edit',$item) }}" class="ft-link-sm">Edit</a>
                        <form action="{{ route('budget-goals.destroy',$item) }}" method="POST"
                              onsubmit="return confirm('Yakin ingin menghapus budget/goal ini?');">
                            @csrf
                            @method('DELETE')
                            <button class="ft-link-sm ft-text-expense" type="submit">Delete</button>
                        </form>
                    </div>
                </div>

                <div class="ft-budget-amount-row">
                    <span>Limit</span>
                    <strong>Rp {{ number_format($item->limit,0,'.',',') }}</strong>
                </div>

                <div class="ft-budget-progress">
                    <div class="ft-budget-bar {{ $barClass }}" style="width: {{ min($usage,100) }}%;"></div>
                </div>

                <div class="ft-budget-usage-row">
                    <span>Usage</span>
                    <span>{{ $usage }} %</span>
                </div>
            </div>
        @empty
            <p>Belum ada budget/goal. Yuk buat yang pertama! 🎯</p>
        @endforelse
    </div>

    {{-- STATISTIK DI BAWAH LIST --}}
    <div class="ft-stats-row">
        <div class="ft-card ft-stat-card">
            <div class="ft-stat-label">Total goals</div>
            <div class="ft-stat-value">{{ $totalGoals }}</div>
        </div>
        <div class="ft-card ft-stat-card">
            <div class="ft-stat-label">Total achieved</div>
            <div class="ft-stat-value">{{ $totalAchieved }}</div>
        </div>
        <div class="ft-card ft-stat-card">
            <div class="ft-stat-label">Total budget</div>
            <div class="ft-stat-value">Rp {{ number_format($totalBudget,0,'.',',') }}</div>
            <div class="ft-stat-sub">Terpakai: Rp {{ number_format($spent,0,'.',',') }}</div>
            <div class="ft-stat-sub">Sisa: Rp {{ number_format($remaining,0,'.',',') }}</div>
        </div>
    </div>
@endsection
