@php
    $percent = min($percent, 150); // safety cap biar ga tembus layout

    if ($percent > 85) {
        $barColor = 'bg-red-500';
    } elseif ($percent >= 60) {
        $barColor = 'bg-yellow-400';
    } else {
        $barColor = 'bg-green-500';
    }
@endphp

<div class="space-y-1">
    <div class="flex justify-between text-sm">
        <span class="font-medium text-black">{{ $name }}</span>
        <span class="text-gray-600">{{ $percent }}%</span>
    </div>

    <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden">
        <div
            class="h-full {{ $barColor }} rounded-full transition-all duration-500"
            style="width: {{ min($percent, 100) }}%"
        ></div>
    </div>

    @if($percent > 100)
        <div class="text-xs text-red-600 font-semibold">
            ⚠️ Melebihi {{ $percent - 100 }}% dari target
        </div>
    @endif
</div>
