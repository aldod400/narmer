<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Pages\DeliverySettings;
use App\Filament\Resources\OrderResource\Widgets\ProfitBarChart;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => '#171640',
                'secondary' => Color::Gray,
                'info' => Color::Cyan,
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger' => Color::Red,
                'purple' => Color::Purple,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Pages\Dashboard::class,

            ])

            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                //
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\WebLang::class,
            ])
            ->authMiddleware([
                Authenticate::class,

            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label(fn() => app()->getLocale() === 'ar' ? 'English' : 'العربية')
                    ->url(fn() => route('lang', ['lang' => app()->getLocale() === 'ar' ? 'en' : 'ar']))
                    ->icon('heroicon-o-language'),
            ])
            ->profile()
            ->sidebarCollapsibleOnDesktop()
            ->globalSearch(false)
            ->brandLogo(asset('assets/img/logo.png'))
            ->brandLogoHeight("50px")
            ->favicon('assets/img/logo.png')
            ->renderHook(
                'panels::body.start',
                fn() => view('filament.pages.firebase-script')
            )
            ->brandName(fn() => __('message.narmer'));
    }
}
