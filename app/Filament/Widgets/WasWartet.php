<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\GroupRegistrations\GroupRegistrationResource;
use App\Filament\Resources\Inquiries\InquiryResource;
use App\Models\GroupRegistration;
use App\Models\Inquiry;
use Filament\Widgets\Widget;

/**
 * „Was wartet auf dich“ auf der Startseite des Panels: neue Anmeldungen zu
 * Selbsthilfegruppen und neue Anfragen, je mit Knopf in die Liste.
 */
class WasWartet extends Widget
{
    /** @var view-string */
    protected string $view = 'filament.widgets.was-wartet';

    protected int|string|array $columnSpan = 'full';

    // Sofort mit Zahlen statt erst leer und dann nachgeladen
    protected static bool $isLazy = false;

    protected function getViewData(): array
    {
        return [
            'kaesten' => [
                [
                    'titel' => 'Anmeldungen zu Selbsthilfegruppen',
                    'anzahl' => GroupRegistration::offen()->count(),
                    'eine' => '1 neue Anmeldung wartet auf deine Freigabe.',
                    'mehrere' => ':anzahl neue Anmeldungen warten auf deine Freigabe.',
                    'keine' => 'Keine neuen Anmeldungen.',
                    'knopf' => 'Anmeldungen ansehen',
                    'url' => GroupRegistrationResource::getUrl('index'),
                    'hilfe' => 'Bei der Anmeldung auf „Freigeben und Link senden“ klicken und bestätigen. '
                        .'Dann bekommt die Person den Teams-Link per E-Mail.',
                ],
                [
                    'titel' => 'Anfragen über die Website',
                    'anzahl' => Inquiry::offen()->count(),
                    'eine' => '1 neue Anfrage.',
                    'mehrere' => ':anzahl neue Anfragen.',
                    'keine' => 'Keine neuen Anfragen.',
                    'knopf' => 'Anfragen ansehen',
                    'url' => InquiryResource::getUrl('index'),
                    'hilfe' => 'Anfrage öffnen, per E-Mail antworten und danach als erledigt markieren.',
                ],
            ],
        ];
    }
}
