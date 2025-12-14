<x-filament-panels::page>
    <div class="ft-trans-layout">

        {{-- HEADER --}}
        <div class="ft-page-header-row">
            <div>
                <h1 class="ft-page-title">Transactions</h1>
                <p class="ft-page-subtitle">
                    Kelola limit dan impian finansial kamu.
                </p>
            </div>

            <a href="{{ route('filament.admin.resources.transactions.create') }}"
               class="fin-btn-outline fin-btn-big">
                + Input Transaction
            </a>
        </div>

        {{-- FILTER --}}
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
                        {{ $tx->date->format('d F Y') }}
                    </div>

                    <div class="ft-col ft-col-category">
                        {{ $tx->category?->name ?? '-' }}
                    </div>

                    <div class="ft-col ft-col-amount">
                        Rp {{ number_format($tx->amount, 0, ',', '.') }}
                    </div>

                    <div class="ft-col ft-col-name">
                        {{ $tx->title }}
                    </div>

                    <div class="ft-col ft-col-type">
                        <span class="ft-pill ft-pill-{{ $tx->type }}">
                            {{ ucfirst($tx->type) }}
                        </span>
                    </div>

                    <div class="ft-col ft-col-actions">
                        <a href="{{ route('filament.admin.resources.transactions.edit', $tx) }}"
                           class="fin-btn-dark fin-btn-sm">
                            Edit
                        </a>
                        <button
                            type="button"
                            class="fin-btn-red fin-btn-sm"
                            wire:click="confirmDelete({{ $tx->id }})">
                            Delete
                        </button>

                    </div>
                </article>
            @empty
                <div class="ft-trans-empty">
                    No transactions yet.
                </div>
            @endforelse
        </section>
    </div>
</x-filament-panels::page>
