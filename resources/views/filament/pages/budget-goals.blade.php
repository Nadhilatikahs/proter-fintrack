{{-- resources/views/filament/pages/budget-goals.blade.php --}}

<x-filament-panels::page>
    <div class="ft-budgets-layout">

        {{-- HEADER + BUTTON ADD --}}
        <div class="ft-budget-header">
            <div>
                <h1 class="ft-page-title">Budget &amp; Goals</h1>
                <p class="ft-page-subtitle">
                    Keep track of your spending limits and long-term goals.
                </p>
            </div>

            <a
                href="{{ route('filament.admin.resources.budget-goals.create') }}"
                class="fin-btn-blue"
            >
                + Add New Budget/Goals
            </a>
        </div>

        {{-- GRID KARTU BUDGET + GOALS --}}
        <section class="ft-budget-grid">
            {{-- BUDGETS --}}
            @forelse($budgets as $item)
                @php
                    $progress = $item->progress ?? 0;
                    $barClass = $progress >= 90 ? 'ft-progress-bar-danger'
                               : ($progress >= 50 ? 'ft-progress-bar-warning'
                               : 'ft-progress-bar-success');
                @endphp

                <article class="ft-budget-card">
                    <div class="ft-budget-header-row">
                        <div>
                            <div class="ft-budget-title">
                                {{ $item->name }}
                            </div>
                            <div class="ft-budget-sub">
                                Budget • Periode: {{ $item->period_label ?? '-' }}
                            </div>
                        </div>

                        <span class="ft-badge-type">Budget</span>
                    </div>

                    <div class="ft-budget-target">
                        Limit: <strong>Rp {{ number_format($item->target_amount ?? 0, 0, ',', '.') }}</strong>
                    </div>

                    <div class="ft-progress-row">
                        <div class="ft-progress-track">
                            <div
                                class="ft-progress-bar {{ $barClass }}"
                                style="width: {{ $progress }}%;"
                            ></div>
                        </div>
                        <div class="ft-progress-info">
                            <span>Progress</span>
                            <span>{{ $progress }}%</span>
                        </div>
                    </div>

                    <div class="ft-budget-footer">
                        <span class="ft-budget-sub">
                            Spent: Rp {{ number_format($item->spent ?? 0, 0, ',', '.') }}
                        </span>

                        <div class="ft-budget-actions">
                            <a
                                href="{{ route('filament.admin.resources.budget-goals.edit', $item) }}"
                                class="fin-btn-dark"
                            >
                                Edit
                            </a>

                            <button
                                type="button"
                                class="fin-btn-red"
                                wire:click="confirmDelete({{ $item->id }})"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </article>
            @empty
            @endforelse

            {{-- GOALS --}}
            @foreach($goals as $item)
                @php
                    $progress = $item->progress ?? 0;
                    $barClass = $progress >= 100 ? 'ft-progress-bar-success'
                               : ($progress >= 50 ? 'ft-progress-bar-warning'
                               : 'ft-progress-bar-danger');
                @endphp

                <article class="ft-budget-card">
                    <div class="ft-budget-header-row">
                        <div>
                            <div class="ft-budget-title">
                                {{ $item->name }}
                            </div>
                            <div class="ft-budget-sub">
                                Goal • Deadline: {{ $item->deadline_label ?? '-' }}
                            </div>
                        </div>

                        <span class="ft-badge-type ft-badge-goal">Goal</span>
                    </div>

                    <div class="ft-budget-target">
                        Target: <strong>Rp {{ number_format($item->target_amount ?? 0, 0, ',', '.') }}</strong>
                    </div>

                    <div class="ft-progress-row">
                        <div class="ft-progress-track">
                            <div
                                class="ft-progress-bar {{ $barClass }}"
                                style="width: {{ $progress }}%;"
                            ></div>
                        </div>
                        <div class="ft-progress-info">
                            <span>Progress</span>
                            <span>{{ $progress }}%</span>
                        </div>
                    </div>

                    <div class="ft-budget-footer">
                        <span class="ft-budget-sub">
                            Saved: Rp {{ number_format($item->saved ?? 0, 0, ',', '.') }}
                        </span>

                        <div class="ft-budget-actions">
                            <a
                                href="{{ route('filament.admin.resources.budget-goals.edit', $item) }}"
                                class="fin-btn-dark"
                            >
                                Edit
                            </a>

                            <button
                                type="button"
                                class="fin-btn-red"
                                wire:click="confirmDelete({{ $item->id }})"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach

            @if($budgets->isEmpty() && $goals->isEmpty())
                <article class="ft-budget-card">
                    <div class="ft-budget-title mb-1">Belum ada budget atau goal.</div>
                    <div class="ft-budget-sub">
                        Klik <strong>+ Add New Budget/Goals</strong> untuk membuat yang pertama.
                    </div>
                </article>
            @endif
        </section>

        {{-- STATISTIK DI BAWAH GRID --}}
        <section class="ft-statistic-card">
            <div class="ft-stat-block">
                <div class="ft-stat-title">Total Goals</div>
                <div class="ft-stat-value">{{ $totalGoals }}</div>
            </div>

            <div class="ft-stat-block">
                <div class="ft-stat-title">Total Achieved</div>
                <div class="ft-stat-value">{{ $totalAchieved }}</div>
            </div>

            <div class="ft-stat-block">
                <div class="ft-stat-title">Total Budget</div>
                <div class="ft-stat-value">
                    Rp {{ number_format($totalBudgetLimit, 0, ',', '.') }}
                </div>
                <div class="ft-budget-sub">
                    Terpakai: Rp {{ number_format($totalBudgetSpent, 0, ',', '.') }}<br>
                    Remaining Budget: Rp {{ number_format($remainingBudget, 0, ',', '.') }}
                </div>
            </div>
        </section>

        {{-- MODAL DELETE ala Figma --}}
        @if($showDeleteModal)
            <div class="ft-modal-backdrop">
                <div class="ft-modal">
                    <div class="ft-modal-title">
                        Delete budget / goal
                    </div>
                    <div class="ft-modal-text">
                        Are you sure you want to delete this item?<br>
                        <span class="ft-modal-warning">
                            This action cannot be undone.
                        </span>
                    </div>
                    <div class="ft-modal-actions">
                        <button
                            type="button"
                            class="fin-btn-dark"
                            wire:click="$set('showDeleteModal', false)"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="fin-btn-red"
                            wire:click="deleteConfirmed"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
