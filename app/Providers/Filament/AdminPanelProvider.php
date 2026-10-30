<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
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
            ->login()
            ->brandName('KE!N EINZELFALL')
            ->favicon(asset('img/logo.png'))
            // Hausfarbe des Vereins statt Filament-Standard — die Redaktion soll
            // sich im Panel wiederfinden.
            ->colors([
                'primary' => Color::hex('#00702F'),
            ])
            ->maxContentWidth('full')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            // Eigene Startseite „Start“ statt Filaments „Dashboard“
            // (App\Filament\Pages\Start, Durchlauf 30.10.2026).
            ->pages([
                \App\Filament\Pages\Start::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            // Ohne „Willkommen“ und Filament-Werbung: Die Startseite zeigt
            // nur, was wartet. Abmelden geht über das Kürzel oben rechts.
            ->widgets([])
            ->middleware([
                // Header fürs Panel (Prüfung der Firma, 08.10.2026)
                \App\Http\Middleware\PanelSicherheitsHeader::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
