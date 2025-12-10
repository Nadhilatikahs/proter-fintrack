{{-- resources/views/filament/pages/reports.blade.php --}}
<x-filament-panels::page>
    <div class="space-y-6">

        {{-- HEADER: Judul & Export PDF --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">
                    Reports
                </h2>
                <p class="text-sm text-slate-600">
                    Cash flow, daily transactions, budget usage, and goal progress.
                </p>
            </div>

            {{-- PILIHAN EXPORT PDF --}}
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-sm font-medium text-slate-700">Export as PDF:</span>

                <button
                    wire:click="exportPdf('cash-flow')"
                    type="button"
                    class="rounded-lg bg-[#1F2937] px-3 py-2 text-xs font-medium text-white shadow-sm hover:bg-[#111827] focus:outline-none focus:ring-2 focus:ring-[#7BAD3E]"
                >
                    Cash Flow
                </button>

                <button
                    wire:click="exportPdf('daily')"
                    type="button"
                    class="rounded-lg bg-[#1F2937] px-3 py-2 text-xs font-medium text-white shadow-sm hover:bg-[#111827] focus:outline-none focus:ring-2 focus:ring-[#7BAD3E]"
                >
                    Daily Transactions
                </button>

                <button
                    wire:click="exportPdf('budget')"
                    type="button"
                    class="rounded-lg bg-[#1F2937] px-3 py-2 text-xs font-medium text-white shadow-sm hover:bg-[#111827] focus:outline-none focus:ring-2 focus:ring-[#7BAD3E]"
                >
                    Budget
                </button>

                <button
                    wire:click="exportPdf('goal')"
                    type="button"
                    class="rounded-lg bg-[#1F2937] px-3 py-2 text-xs font-medium text-white shadow-sm hover:bg-[#111827] focus:outline-none focus:ring-2 focus:ring-[#7BAD3E]"
                >
                    Goals
                </button>
            </div>
        </div>

        {{-- TAB PILIHAN REPORT --}}
        <div class="flex flex-wrap items-center gap-2 rounded-2xl bg-white/70 p-2 shadow-sm">
            @php
                $tabs = [
                    'cash-flow' => 'Cash Flow',
                    'budget'    => 'Budget',
                    'daily'     => 'Daily',
                    'goal'      => 'Goal',
                ];
            @endphp

            @foreach ($tabs as $value => $label)
                <button
                    type="button"
                    wire:click="$set('tab', '{{ $value }}')"
                    @class([
                        'rounded-xl px-4 py-2 text-xs font-medium transition',
                        $this->tab === $value
                            ? 'bg-[#7BAD3E] text-white shadow-sm'
                            : 'bg-transparent text-slate-700 hover:bg-[#EFF6D2]',
                    ])
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- FILTER PERIODE --}}
        <div class="flex flex-wrap items-end gap-4 rounded-2xl bg-white/70 p-4 shadow-sm">
            <div class="flex flex-col gap-2">
                <label class="text-xs font-medium text-slate-700">Mode Periode</label>

                <select
                    wire:model.live="periodMode"
                    class="block w-44 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-[#7BAD3E] focus:outline-none focus:ring-1 focus:ring-[#7BAD3E]"
                >
                    <option value="day">Per Hari</option>
                    <option value="month">Per Bulan</option>
                    <option value="year">Per Tahun</option>
                </select>
            </div>

            <div class="flex flex-wrap items-end gap-3">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-700">
                        Dari
                        @if ($this->periodMode === 'day') tanggal
                        @elseif ($this->periodMode === 'month') bulan
                        @else tahun
                        @endif
                    </label>

                    <input
                        type="date"
                        wire:model.live="fromDate"
                        class="block rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-[#7BAD3E] focus:outline-none focus:ring-1 focus:ring-[#7BAD3E]"
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-700">
                        Sampai
                        @if ($this->periodMode === 'day') tanggal
                        @elseif ($this->periodMode === 'month') bulan
                        @else tahun
                        @endif
                    </label>

                    <input
                        type="date"
                        wire:model.live="toDate"
                        class="block rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-[#7BAD3E] focus:outline-none focus:ring-1 focus:ring-[#7BAD3E]"
                    />
                </div>

                <button
                    type="button"
                    wire:click="applyFilters"
                    class="inline-flex items-center rounded-xl bg-[#7BAD3E] px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-[#659531] focus:outline-none focus:ring-2 focus:ring-[#7BAD3E] focus:ring-offset-2"
                >
                    Apply Filter
                </button>
            </div>
        </div>

        {{-- ISI PER TAB --}}
        <div class="space-y-6">
            @if ($this->tab === 'cash-flow')
                @include('filament.pages.reports._cash_flow')
            @elseif ($this->tab === 'budget')
                @include('filament.pages.reports._budget')
            @elseif ($this->tab === 'daily')
                @include('filament.pages.reports._daily')
            @elseif ($this->tab === 'goal')
                @include('filament.pages.reports._goal')
            @endif
        </div>
    </div>
</x-filament-panels::page>
