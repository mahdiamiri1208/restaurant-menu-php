<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
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

    protected Width|string|null $maxContentWidth = Width::FourExtraLarge;

    public ?array $data = [];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات ورود')
                    ->description('برای امنیت بیشتر، از یک رمز یکتا و طولانی استفاده کنید.')
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->schema([
                        TextInput::make('current_password')
                            ->label('رمز عبور فعلی')
                            ->password()
                            ->revealable()
                            ->currentPassword(guard: Filament::getAuthGuard())
                            ->autocomplete('current-password')
                            ->required()
                            ->minLength(8)
                            ->maxLength(128)
                            ->dehydrated(false),
                        TextInput::make('new_password')
                            ->label('رمز عبور جدید')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->required()
                            ->minLength(12)
                            ->maxLength(128)
                            ->helperText('حداقل ۱۲ کاراکتر؛ ترکیب حروف، عدد و نماد امن‌تر است.')
                            ->confirmed(),
                        TextInput::make('new_password_confirmation')
                            ->label('تکرار رمز عبور جدید')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->required()
                            ->dehydrated(false),
                    ]),
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
            ->title('رمز عبور با موفقیت تغییر کرد.')
            ->body('از این پس با رمز جدید وارد شوید.')
            ->success()
            ->send();
    }

    public function getSubheading(): ?string
    {
        return 'رمز عبور حساب مدیر را به‌صورت دوره‌ای تغییر دهید و آن را در جای امن نگه دارید.';
    }

    public function getTitle(): string|Htmlable
    {
        return 'تغییر رمز عبور';
    }
}
