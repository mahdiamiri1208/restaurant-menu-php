<form wire:submit="save" class="space-y-6">
    {{ $this->form }}

    <x-filament::button type="submit">
        ذخیره تنظیمات
    </x-filament::button>
</form>
