{{-- resources/views/filament/pages/reports/_budget.blade.php --}}
<div
    x-data="budgetChart(
        {{ json_encode($budget['labels']) }},
        {{ json_encode($budget['limit']) }},
        {{ json_encode($budget['used']) }}
    )"
    x-init="render()"
    class="grid gap-6 lg:grid-cols-3"
>
    {{-- Horizontal bar chart --}}
    <div class="p-4 bg-lime-200/80 rounded-3xl shadow-sm lg:col-span-2">
        <h2 class="mb-2 text-lg font-semibold text-gray-900">
            Budget usage by category
        </h2>
        <p class="mb-4 text-sm text-gray-700">
            Perbandingan limit vs pemakaian untuk setiap budget yang kamu set.
        </p>

        <div class="h-80">
            <canvas x-ref="canvas"></canvas>
        </div>
    </div>

    {{-- Summary --}}
    <div class="flex flex-col gap-4">
        <div class="p-4 bg-lime-200/80 rounded-3xl shadow-sm">
            <p class="text-xs font-medium text-gray-600 uppercase">Total budget</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">
                Rp {{ number_format($budget['summary']['total_limit'] ?? 0, 0, '.', ',') }}
            </p>
        </div>

        <div class="p-4 bg-lime-200/80 rounded-3xl shadow-sm">
            <p class="text-xs font-medium text-gray-600 uppercase">Used</p>
            <p class="mt-1 text-2xl font-bold text-orange-600">
                Rp {{ number_format($budget['summary']['total_used'] ?? 0, 0, '.', ',') }}
            </p>
        </div>

        <div class="p-4 bg-lime-200/80 rounded-3xl shadow-sm">
            <p class="text-xs font-medium text-gray-600 uppercase">Remaining</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700">
                Rp {{ number_format($budget['summary']['remaining'] ?? 0, 0, '.', ',') }}
            </p>
        </div>
    </div>
</div>
