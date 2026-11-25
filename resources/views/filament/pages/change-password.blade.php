<x-filament-panels::page>
    <form wire:submit="save" class="admin-page-form">
        {{ $this->form }}

        <div class="admin-page-form-actions">
            <x-filament::button
                type="submit"
                size="lg"
                :icon="\Filament\Support\Icons\Heroicon::OutlinedKey"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                تغییر رمز عبور
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
