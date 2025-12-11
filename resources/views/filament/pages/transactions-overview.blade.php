<x-filament-panels::page>
    <div class="ft-trans-layout">

        {{-- HEADER + BUTTON --}}
        <div class="ft-page-header-row">
            <h1 class="ft-page-title">Transactions</h1>

            <a href="{{ route('filament.admin.resources.transactions.create') }}"
               class="fin-btn-outline fin-btn-big">
                + Input Transaction
            </a>
        </div>

        {{-- FILTER PILLS --}}
        @php
            $filters = [
                'all'      => 'All',
                'day'      => 'Day',
                'month'    => 'Month',
                'category' => 'Category',
            ];
        @endphp

        <div class="ft-filter-row">
            @foreach ($filters as $key => $label)
                <a href="{{ route('filament.admin.pages.transactions-overview', ['filter' => $key]) }}"
                   class="ft-filter-pill {{ $activeFilter === $key ? 'is-active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($activeFilter === 'category')
            <form method="GET"
                  action="{{ route('filament.admin.pages.transactions-overview') }}"
                  class="ft-filter-category-form">
                <input type="hidden" name="filter" value="category">
                <select name="category_id"
                        class="ft-select-light"
                        onchange="this.form.submit()">
                    <option value="">All categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}"
                            @selected($activeCategoryId == $cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif

        {{-- TABLE --}}
        <section class="ft-trans-table">
            <header class="ft-trans-row ft-trans-header">
                <div class="ft-col ft-col-date">Date</div>
                <div class="ft-col ft-col-category">Category</div>
                <div class="ft-col ft-col-amount">Amount</div>
                <div class="ft-col ft-col-name">Name</div>
                <div class="ft-col ft-col-type">Type</div>
                <div class="ft-col ft-col-actions"></div>
            </header>

            @forelse ($transactions as $tx)
                <article class="ft-trans-row">
                    <div class="ft-col ft-col-date">
                        {{ \Carbon\Carbon::parse($tx->date)->format('d F Y') }}
                    </div>

                    <div class="ft-col ft-col-category">
                        @if ($tx->category)
                            <span class="ft-pill ft-pill-category">
                                {{ $tx->category->name }}
                            </span>
                        @else
                            <span class="ft-pill ft-pill-muted">-</span>
                        @endif
                    </div>

                    <div class="ft-col ft-col-amount">
                        Rp {{ number_format($tx->amount ?? 0, 0, ',', '.') }}
                    </div>

                    <div class="ft-col ft-col-name">
                        {{ $tx->title }}
                    </div>

                    <div class="ft-col ft-col-type">
                        @if ($tx->type === 'income')
                            <span class="ft-pill ft-pill-income">Income</span>
                        @else
                            <span class="ft-pill ft-pill-expense">Expense</span>
                        @endif
                    </div>

                    <div class="ft-col ft-col-actions">
                        <a href="{{ route('filament.admin.resources.transactions.edit', $tx) }}"
                           class="fin-btn-dark fin-btn-sm">
                            Edit
                        </a>
                        <button type="button"
                                class="fin-btn-red fin-btn-sm"
                                wire:click="confirmDelete({{ $tx->id }})">
                            Delete
                        </button>
                    </div>
                </article>
            @empty
                <div class="ft-trans-empty">
                    No transactions yet. Click <strong>+ Input Transaction</strong> to add one.
                </div>
            @endforelse
        </section>

        {{-- MODAL DELETE --}}
        @if ($showDeleteModal)
            <div class="ft-modal-backdrop">
                <div class="ft-modal">
                    <div class="ft-modal-title">
                        Delete transaction
                    </div>
                    <div class="ft-modal-text">
                        Are you sure you want to delete this transaction?
                        <br>
                        <span class="text-red-600 font-semibold">
                            This action cannot be undone.
                        </span>
                    </div>
                    <div class="ft-modal-actions">
                        <button type="button"
                                class="fin-btn-dark"
                                wire:click="$set('showDeleteModal', false)">
                            Cancel
                        </button>
                        <button type="button"
                                class="fin-btn-red"
                                wire:click="deleteConfirmed">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
