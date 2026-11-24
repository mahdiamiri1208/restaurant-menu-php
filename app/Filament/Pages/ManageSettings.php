<?php

namespace App\Filament\Pages;

use App\Filament\Support\MenuImageUpload;
use App\Models\Settings;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class ManageSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'تنظیمات رستوران';

    protected static string|UnitEnum|null $navigationGroup = 'تنظیمات';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'settings';

    protected string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = $this->getSettingsRecord();

        $this->form->fill($settings->exists ? $settings->only([
            'restaurant_name',
            'logo_url',
        ]) : [
            'restaurant_name' => 'پاریس لانژ',
            'logo_url' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->record($this->getSettingsRecord())
            ->components([
                TextInput::make('restaurant_name')
                    ->label('نام رستوران')
                    ->required()
                    ->minLength(2)
                    ->maxLength(100)
                    ->trim()
                    ->autofocus(),
                MenuImageUpload::make('logo_url', 'لوگوی رستوران'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        DB::transaction(function () use ($data): void {
            $this->getSettingsRecord()->fill($data)->save();
        });

        Notification::make()
            ->title('تنظیمات ذخیره شد.')
            ->success()
            ->send();
    }

    protected function getSettingsRecord(): Settings
    {
        return Settings::query()->firstOrNew(['id' => 1]);
    }

    public function getTitle(): string|Htmlable
    {
        return 'تنظیمات رستوران';
    }
}
