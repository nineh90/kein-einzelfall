<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Start;
use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\GroupRegistrations\GroupRegistrationResource;
use App\Filament\Resources\Inquiries\InquiryResource;
use App\Filament\Verein\Resources\TeamsLinks\TeamsLinkResource;
use App\Filament\Widgets\WasWartet;
use Filament\Http\Middleware\Authenticate;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;

/**
 * Vereinsbereich unter /intern (Kevin und Nils, 30.10.2026).
 *
 * Der Verein bekommt keinen Zugang zur Verwaltung, sonst könnte er Seiten,
 * Menüs und Texte der Website ändern. Hier gibt es nur, was er im Alltag
 * braucht: Veranstaltungen, Anmeldungen zu den Selbsthilfegruppen, Anfragen
 * und die Teams-Links der Gruppen. Die Bereiche werden einzeln aufgezählt
 * statt gesucht, damit ein neuer Bereich nicht von selbst hier auftaucht.
 *
 * Pfad /intern, weil /verein die öffentliche Vereinsseite ist.
 */
class VereinPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('verein')
            ->path('intern')
            ->login()
            // Passwort und E-Mail selbst ändern, nur mit dem alten Passwort
            // (Kevin, 30.10.2026: Taddi bekommt ein Startpasswort von ihm).
            ->profile(isSimple: false)
            ->brandName('KE!N EINZELFALL')
            ->favicon(asset('img/logo.png'))
            ->colors([
                'primary' => Color::hex('#00702F'),
            ])
            ->maxContentWidth('full')
            ->resources([
                EventResource::class,
                GroupRegistrationResource::class,
                InquiryResource::class,
                TeamsLinkResource::class,
            ])
            ->pages([
                Start::class,
            ])
            ->widgets([
                WasWartet::class,
            ])
            ->middleware(AdminPanelProvider::middleware())
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
