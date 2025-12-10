{{-- resources/views/filament/pages/profile.blade.php --}}

<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit.prevent="save" class="space-y-6 max-w-3xl">
            {{ $this->form }}

            <div class="flex items-center gap-3">
                <x-filament::button type="submit">
                    Save changes
                </x-filament::button>

                <x-filament::button
                    type="button"
                    color="gray"
                    outlined
                    wire:click="$refresh"
                >
                    Reset
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
