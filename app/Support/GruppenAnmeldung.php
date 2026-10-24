<?php

namespace App\Support;

use App\Mail\AnmeldungEingegangen;
use App\Mail\GruppenZugang;
use App\Models\Group;
use App\Models\GroupRegistration;
use App\Notifications\NeueGruppenAnmeldung;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

/**
 * Anmeldung zu einer Selbsthilfegruppe (KEV-226), für das Formular auf der
 * Gruppenseite und die Freigabe im Verwaltungsbereich.
 *
 * Taddis Wunsch: über die Website anmelden, Teilnahme- und
 * Verschwiegenheitsvereinbarung und Gruppenregeln akzeptieren, den
 * Zugangslink automatisch bekommen. Voreinstellung ist trotzdem die Freigabe
 * durch den Verein (Kevin, 24.10.2026), weil die Gruppen nur für Betroffene
 * sind. Je Gruppe lässt sich im Panel auf „sofort verschicken“ umstellen.
 *
 * Ein Ausfall des Mailversands verschluckt nie eine Anmeldung: Sie liegt
 * dann schon in der Datenbank, und der Verein sieht sie im Panel.
 */
class GruppenAnmeldung
{
    /**
     * Speichert die Anmeldung und verschickt, was dazugehört.
     *
     * @return 'link'|'bestaetigt'|'gespeichert' was die Person bekommen hat
     */
    public static function annehmen(Group $gruppe, string $email, ?string $name): string
    {
        $anmeldung = $gruppe->anmeldungen()->create([
            'name' => $name,
            'email' => $email,
            'locale' => app()->getLocale(),
            'status' => 'offen',
            'zugestimmt_at' => now(),
        ]);

        $ergebnis = 'gespeichert';

        if ($gruppe->link_automatisch && filled($gruppe->zugangslink)) {
            $ergebnis = self::zugangSenden($anmeldung) ? 'link' : 'gespeichert';
        } elseif (self::senden($anmeldung, new AnmeldungEingegangen($gruppe, $name))) {
            $ergebnis = 'bestaetigt';
        }

        self::vereinBenachrichtigen($anmeldung->fresh());

        return $ergebnis;
    }

    /**
     * Freigabe: Zugangslink verschicken und die Anmeldung als freigegeben
     * vermerken. Ohne Link bei der Gruppe oder ohne Mailversand passiert
     * nichts, die Anmeldung bleibt offen.
     */
    public static function zugangSenden(GroupRegistration $anmeldung): bool
    {
        $gruppe = $anmeldung->group;

        if (! $gruppe || blank($gruppe->zugangslink)) {
            return false;
        }

        if (! self::senden($anmeldung, new GruppenZugang($gruppe, $anmeldung->name))) {
            return false;
        }

        $anmeldung->update(['status' => 'freigegeben', 'link_versendet_at' => now()]);

        return true;
    }

    /**
     * Die beiden Dokumente, denen man bei der Anmeldung zustimmt, aus der
     * Dokumentenliste von /selbsthilfegruppen. So kommt ein im Panel neu
     * hochgeladenes PDF auch im Formular an. Fehlt eins, zeigt der Link auf
     * die Liste.
     *
     * @return array{teilnahme: string, gruppenregeln: string}
     */
    public static function dokumente(): array
    {
        $liste = sprachlink('page', ['slug' => 'selbsthilfegruppen']).'#dl-dokumente-zum-herunterladen';
        $dokumente = \App\Models\Page::where('slug', 'selbsthilfegruppen')->where('locale', \App\Models\Language::standardCode())
            ->first()?->blocks()->where('typ', 'download_list')->get()
            ->flatMap(fn ($b) => $b->data['dokumente'] ?? []) ?? collect();

        $finde = fn (string $wort) => (function () use ($dokumente, $wort, $liste) {
            $dok = $dokumente->first(fn ($d) => str_contains(mb_strtolower($d['titel'] ?? ''), $wort));
            $url = $dok ? (filled($dok['datei'] ?? null) ? $dok['datei'] : ($dok['url'] ?? null)) : null;

            return $url ?: $liste;
        })();

        return ['teilnahme' => $finde('teilnahme'), 'gruppenregeln' => $finde('gruppenregeln')];
    }

    /**
     * Auf dem Server nur mit echtem Mailversand (wie BeschwerdeController):
     * Mit MAIL_MAILER=log stünden Adresse und Zugangslink im Klartext in
     * storage/logs. Lokal bleibt das Log der Weg, es auszuprobieren.
     */
    public static function versandMoeglich(): bool
    {
        return ! (app()->isProduction() && config('mail.default') === 'log');
    }

    private static function senden(GroupRegistration $anmeldung, \Illuminate\Mail\Mailable $mail): bool
    {
        if (! self::versandMoeglich()) {
            return false;
        }

        try {
            Mail::to($anmeldung->email)->locale($anmeldung->locale ?: 'de')->send($mail);

            return true;
        } catch (\Throwable $e) {
            // Nur die Meldung des Mailservers, keine Adresse
            Log::error('Mail zur Gruppenanmeldung nicht verschickt', [
                'anmeldung_id' => $anmeldung->id,
                'fehler' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private static function vereinBenachrichtigen(GroupRegistration $anmeldung): void
    {
        $empfaenger = config('mail.anmeldungen_an');

        if (blank($empfaenger) || ! self::versandMoeglich()) {
            return;
        }

        try {
            Notification::route('mail', $empfaenger)->notify(new NeueGruppenAnmeldung($anmeldung));
        } catch (\Throwable $e) {
            Log::error('Hinweis auf neue Gruppenanmeldung nicht verschickt', [
                'anmeldung_id' => $anmeldung->id,
                'fehler' => $e->getMessage(),
            ]);
        }
    }
}
