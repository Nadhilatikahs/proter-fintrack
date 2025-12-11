<x-filament-panels::page>
    <div class="ft-budgets-layout">

        {{-- HEADER --}}
        <div class="ft-budget-header">
            <div>
                <h1 class="ft-page-title">Budget &amp; Goals</h1>
                <p class="ft-page-subtitle">
                    Kelola limit dan impian finansial kamu.
                </p>
            </div>

            <a
                href="{{ route('filament.admin.resources.budget-goals.create') }}"
                class="fin-btn-blue"
            >
                + Add New Budget/Goals
            </a>
        </div>

        {{-- GRID BUDGET & GOAL (card seperti Figma) --}}
        <section class="ft-budget-grid">
            {{-- BUDGET cards --}}
            @foreach ($budgets as $item)
                @php
                    $progress = $item->progress ?? 0;
                    $barClass = $progress >= 90 ? 'ft-progress-bar-danger' : 'ft-progress-bar-ok';
                @endphp

                <article class="ft-budget-card">
                    <header class="ft-budget-card-header">
                        <div>
                            <div class="ft-budget-card-title">{{ $item->name }}</div>
                            <div class="ft-budget-card-sub">
                                Limit : Rp {{ number_format($item->target_amount ?? 0, 0, ',', '.') }}
                            </div>
                            <div class="ft-budget-card-sub">
                                Period : {{ $item->period_label ?? '-' }}
                            </div>
                        </div>

                        <span class="ft-pill ft-pill-budget">Budget</span>
                    </header>

                    <div class="ft-budget-card-body">
                        <div class="ft-progress-label-row">
                            <span>Progress</span>
                            <span>{{ $progress }}%</span>
                        </div>
                        <div class="ft-progress-track">
                            <div class="ft-progress-bar {{ $barClass }}" style="width: {{ $progress }}%"></div>
                        </div>
                        <div class="ft-budget-card-sub mt-2">
                            Spent :
                            <strong>Rp {{ number_format($item->spent ?? 0, 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    <footer class="ft-budget-card-footer">
                        <button
                            type="button"
                            onclick="window.location='{{ route('filament.admin.resources.budget-goals.edit', $item) }}'"
                            class="fin-btn-dark"
                        >
                            Edit
                        </button>
                        <button
                            type="button"
                            class="fin-btn-red"
                            wire:click="confirmDelete({{ $item->id }})"
                        >
                            Delete
                        </button>
                    </footer>
                </article>
            @endforeach

            {{-- GOAL cards --}}
            @foreach ($goals as $item)
                @php
                    $progress = $item->progress ?? 0;
                @endphp

                <article class="ft-budget-card">
                    <header class="ft-budget-card-header">
                        <div>
                            <div class="ft-budget-card-title">{{ $item->name }}</div>
                            <div class="ft-budget-card-sub">
                                Target : Rp {{ number_format($item->target_amount ?? 0, 0, ',', '.') }}
                            </div>
                            <div class="ft-budget-card-sub">
                                Deadline : {{ $item->deadline_label ?? '-' }}
                            </div>
                        </div>

                        <span class="ft-pill ft-pill-goal">Goal</span>
                    </header>

                    <div class="ft-budget-card-body">
                        <div class="ft-progress-label-row">
                            <span>Progress</span>
                            <span>{{ $progress }}%</span>
                        </div>
                        <div class="ft-progress-track">
                            <div class="ft-progress-bar ft-progress-bar-ok" style="width: {{ $progress }}%"></div>
                        </div>
                        <div class="ft-budget-card-sub mt-2">
                            Saved :
                            <strong>Rp {{ number_format($item->saved ?? 0, 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    <footer class="ft-budget-card-footer">
                        <button
                            type="button"
                            onclick="window.location='{{ route('filament.admin.resources.budget-goals.edit', $item) }}'"
                            class="fin-btn-dark"
                        >
                            Edit
                        </button>
                        <button
                            type="button"
                            class="fin-btn-red"
                            wire:click="confirmDelete({{ $item->id }})"
                        >
                            Delete
                        </button>
                    </footer>
                </article>
            @endforeach

            @if ($budgets->isEmpty() && $goals->isEmpty())
                <article class="ft-budget-card">
                    <div class="ft-budget-card-title mb-1">
                        Belum ada budget atau goal.
                    </div>
                    <div class="ft-budget-card-sub">
                        Klik <strong>+ Add New Budget/Goals</strong> untuk membuat yang pertama.
                    </div>
                </article>
            @endif
        </section>

        {{-- STATISTIC CARD (bagian bawah seperti Figma) --}}
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
                <div class="ft-stat-title">Remaining Budget</div>
                <div class="ft-stat-value">
                    Rp {{ number_format($remainingBudget, 0, ',', '.') }}
                </div>
                <div class="ft-budget-card-sub">
                    Total Budget : Rp {{ number_format($totalBudgetLimit, 0, ',', '.') }}<br>
                    Spent : Rp {{ number_format($totalBudgetSpent, 0, ',', '.') }}
                </div>
            </div>
        </section>

        {{-- MODAL DELETE (background gelap, popup terang) --}}
        @if ($showDeleteModal)
            <div class="ft-modal-backdrop">
                <div class="ft-modal">
                    <div class="ft-modal-title">Delete budget / goal</div>
                    <div class="ft-modal-text">
                        Are you sure you want to delete this item?
                        <br>
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
