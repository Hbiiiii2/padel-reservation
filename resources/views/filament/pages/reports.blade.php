<x-filament-panels::page>
    <x-filament-panels::form wire:submit="generateReport">
        {{ $this->form }}

        <div class="flex justify-end mt-4">
            <x-filament::button type="submit">
                Generate Report
            </x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>
