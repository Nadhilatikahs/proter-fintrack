{{-- resources/views/filament/pages/reports/_goal.blade.php --}}
<div
    x-data="goalChart({{ json_encode($goals) }})"
    x-init="render()"
    class="grid gap-6 lg:grid-cols-3"
>
    {{-- Donut chart --}}
    <div class="p-4 bg-lime-200/80 rounded-3xl shadow-sm lg:col-span-2">
        <h2 class="mb-2 text-lg font-semibold text-gray-900">
            Goals progress
        </h2>
        <p class="mb-4 text-sm text-gray-700">
            Perbandingan jumlah goals yang sudah tercapai dan yang masih berjalan.
        </p>

        <div class="h-72">
            <canvas x-ref="canvas"></canvas>
        </div>
    </div>

    {{-- Summary --}}
    <div class="flex flex-col gap-4">
        <div class="p-4 bg-lime-200/80 rounded-3xl shadow-sm">
            <p class="text-xs font-medium text-gray-600 uppercase">Total goals</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">
                {{ $goals['total'] ?? 0 }}
            </p>
        </div>

        <div class="p-4 bg-lime-200/80 rounded-3xl shadow-sm">
            <p class="text-xs font-medium text-gray-600 uppercase">Achieved</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700">
                {{ $goals['done'] ?? 0 }}
            </p>
        </div>

        <div class="p-4 bg-lime-200/80 rounded-3xl shadow-sm">
            <p class="text-xs font-medium text-gray-600 uppercase">In progress</p>
            <p class="mt-1 text-2xl font-bold text-orange-500">
                {{ $goals['running'] ?? 0 }}
            </p>
        </div>
    </div>
</div>
