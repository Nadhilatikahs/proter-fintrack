{{-- resources/views/filament/pages/finance-report.blade.php --}}

<x-filament-panels::page>
    {{-- Kalau mau, di sini bisa ditambah konten lain di atas tabel --}}
    <div class="space-y-4">
        {{-- Tabel laporan --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
