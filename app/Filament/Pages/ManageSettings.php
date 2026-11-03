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
        $settings = Settings::query()->find(1);

        $this->form->fill($settings?->only([
            'restaurant_name',
            'logo_url',
            'phone',
            'address',
        ]) ?? [
            'restaurant_name' => 'رستوران نارنج',
            'logo_url' => null,
            'phone' => null,
            'address' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('restaurant_name')
                    ->label('نام رستوران')
                    ->required()
                    ->minLength(2)
                    ->maxLength(100)
                    ->trim()
                    ->autofocus(),
                MenuImageUpload::make('logo_url', 'لوگوی رستوران'),
                TextInput::make('phone')
                    ->label('شماره تماس')
                    ->maxLength(32)
                    ->trim()
                    ->nullable(),
                TextInput::make('address')
                    ->label('نشانی')
                    ->maxLength(300)
                    ->trim()
                    ->nullable()
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Settings::query()->updateOrCreate(['id' => 1], $data);

        Notification::make()
            ->title('تنظیمات ذخیره شد.')
            ->success()
            ->send();
    }

    public function getTitle(): string|Htmlable
    {
        return 'تنظیمات رستوران';
    }
}
