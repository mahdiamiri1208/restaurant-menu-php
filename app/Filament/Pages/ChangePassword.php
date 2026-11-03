<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class ChangePassword extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = 'تغییر رمز عبور';

    protected static string|UnitEnum|null $navigationGroup = 'تنظیمات';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'change-password';

    protected string $view = 'filament.pages.change-password';

    public ?array $data = [];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('current_password')
                    ->label('رمز عبور فعلی')
                    ->password()
                    ->currentPassword(guard: Filament::getAuthGuard())
                    ->autocomplete('current-password')
                    ->required()
                    ->minLength(8)
                    ->maxLength(128)
                    ->dehydrated(false),
                TextInput::make('new_password')
                    ->label('رمز عبور جدید')
                    ->password()
                    ->autocomplete('new-password')
                    ->required()
                    ->minLength(12)
                    ->maxLength(128)
                    ->confirmed(),
                TextInput::make('new_password_confirmation')
                    ->label('تکرار رمز عبور جدید')
                    ->password()
                    ->autocomplete('new-password')
                    ->required()
                    ->dehydrated(false),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = Filament::auth()->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $user->forceFill(['password' => $data['new_password']])->save();
        $this->form->fill();

        Notification::make()
            ->title('رمز عبور تغییر کرد.')
            ->success()
            ->send();
    }

    public function getTitle(): string|Htmlable
    {
        return 'تغییر رمز عبور';
    }
}
