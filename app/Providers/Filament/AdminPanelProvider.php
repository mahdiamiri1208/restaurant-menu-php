<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use App\Models\Settings;
use Filament\Enums\ThemeMode;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName(fn (): string => $this->restaurantName())
            ->brandLogo(fn (): \Illuminate\Contracts\View\View => view('filament.components.brand', [
                'name' => $this->restaurantName(),
            ]))
            ->brandLogoHeight('3.5rem')
            ->login(Login::class)
            ->font('Vazirmatn', asset('css/filament-font.css'), LocalFontProvider::class)
            ->colors([
                'primary' => Color::hex('#c28b32'),
            ])
            ->darkMode(false)
            ->defaultThemeMode(ThemeMode::Light)
            ->sidebarWidth('17rem')
            ->maxContentWidth(Width::SevenExtraLarge)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->navigationGroups([
                NavigationGroup::make('مدیریت منو'),
                NavigationGroup::make('تنظیمات'),
            ])
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                static fn () => view('filament.components.sidebar-footer'),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                static fn () => view('filament.components.login-footer'),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    private function restaurantName(): string
    {
        return (string) (Settings::query()->value('restaurant_name') ?: 'پاریس لانژ');
    }
}
