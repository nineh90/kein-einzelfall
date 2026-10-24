<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\Textpflege;
use Illuminate\Database\Seeder;

/**
 * Die Datenschutzerklärung beschreibt, was diese Website tut, nicht mehr die
 * WordPress-Altseite (nach KEV-163).
 *
 * Aus der Altseite stammten drei Stellen, die nicht mehr stimmten:
 *
 *  - „Einwilligungstool (Real Cookie Banner)“: Ein Cookie-Banner gibt es hier
 *    nicht, es gibt auch nichts, wofür er nötig wäre.
 *  - „Cookies“ sprach von Statistik-, Marketing- und Drittanbieter-Cookies.
 *    Gesetzt werden genau zwei, beide technisch notwendig.
 *  - „Kontaktformular“ nannte die IP-Adresse in Logfiles und „gelöscht, wenn
 *    die Konversation beendet ist“. Tatsächlich: Nachrichten verschlüsselt,
 *    keine IP-Adresse (App\Support\SitzungOhneSpuren, Formularbremse), feste
 *    Löschfristen (config/anfragen.php), Beschwerden an die Ombudsstelle gar
 *    nicht gespeichert.
 *
 * Und es fehlten zwei: der Speicher im Browser (config/speicher.php verlangt,
 * dass die Datenschutzerklärung ihn nennt; der Baustein „Gespeicherte
 * Einstellungen“ am Seitenende baut die Liste von dort und bleibt so
 * vollständig) und die Spendenformulare von betterplace.org, die erst nach
 * einem Klick laden.
 *
 * Ersetzt wird nur, wo noch exakt der Text der Altseite steht. Was der Verein
 * im Panel geändert hat, bleibt. Läuft beim Import (AltseiteSeeder) und per
 * Migration auf bestehenden Datenbanken.
 */
class DatenschutzSeeder extends Seeder
{
    public const SLUG = 'datenschutz';

    public const ALT_EINWILLIGUNGSTOOL = 'Einwilligungstool (Real Cookie Banner)';

    public const COOKIES = [
        'Diese Website setzt nur zwei Cookies. Beide stammen von uns selbst und sind technisch notwendig. '
        .'Cookies von Drittanbietern, für Statistik oder für Werbung setzen wir nicht. Deshalb gibt es hier '
        .'auch keinen Cookie-Banner.',
        'Sitzungs-Cookie (Name endet auf „-session“): verbindet Ihre Seitenaufrufe zu einer Sitzung, damit '
        .'etwa ein Formular nach einem Tippfehler mit Ihrem Text zurückkommt. Die Sitzung speichern wir '
        .'verschlüsselt und ohne IP-Adresse oder Browserkennung.',
        'XSRF-TOKEN: schützt die Formulare davor, dass fremde Websites in Ihrem Namen etwas absenden.',
        'Beide Cookies enthalten keine Angaben zu Ihrer Person und werden nach zwei Stunden ohne Seitenaufruf '
        .'ungültig.',
        'Rechtsgrundlage ist § 25 Abs. 2 Nr. 2 TDDDG, weil die Cookies unbedingt erforderlich sind, um die '
        .'Website und ihre Formulare bereitzustellen, sowie Art. 6 Abs. 1 lit. f DSGVO (berechtigtes Interesse '
        .'an einer sicheren, funktionierenden Website).',
        'Sie können Cookies in Ihrem Browser jederzeit löschen oder blockieren. Dann lassen sich die Formulare '
        .'auf dieser Website nicht mehr absenden, alles andere funktioniert weiter.',
    ];

    public const SPEICHER = [
        'titel' => 'Speicher im Browser (Local Storage)',
        'absaetze' => [
            'Einige Einstellungen merkt sich diese Website im Speicher Ihres Browsers (Local Storage, für die '
            .'Dauer eines Besuchs Session Storage), und zwar nur auf Ihrem Gerät. Das sind keine Cookies: Nichts '
            .'davon wird an uns übertragen, und nichts davon enthält eine Kennung, über die wir Sie '
            .'wiedererkennen könnten.',
            'Rechtsgrundlage ist § 25 Abs. 2 Nr. 2 TDDDG, weil die Speicherung unbedingt erforderlich ist, um '
            .'Funktionen bereitzustellen, die Sie nutzen, sowie Art. 6 Abs. 1 lit. f DSGVO.',
            'Was im Einzelnen gespeichert werden kann, steht vollständig in der Liste [am Ende dieser '
            .'Seite](/datenschutz#gespeicherte-einstellungen). Dort können Sie jeden Eintrag auch zurücksetzen. '
            .'Ebenso können Sie den Speicher in Ihren Browser-Einstellungen löschen.',
        ],
    ];

    public const BETTERPLACE = [
        'titel' => 'Spendenformulare von betterplace.org',
        'absaetze' => [
            'Auf unseren Seiten zum Spenden können Sie Spendenformulare von betterplace.org anzeigen lassen. '
            .'Anbieter ist die betterplace.org gGmbH, Schlesische Straße 26, 10997 Berlin.',
            'Die Formulare werden erst geladen, wenn Sie das ausdrücklich anklicken. Vorher stellt Ihr Browser '
            .'keine Verbindung zu betterplace.org her. Nach dem Klick lädt er das Formular direkt von '
            .'betterplace.org und überträgt dabei technisch notwendige Daten wie Ihre IP-Adresse. '
            .'betterplace.org kann dabei eigene Cookies setzen. Für diese Verarbeitung ist betterplace.org '
            .'selbst verantwortlich.',
            'Rechtsgrundlage ist Ihre Einwilligung durch den Klick (Art. 6 Abs. 1 lit. a DSGVO und § 25 Abs. 1 '
            .'TDDDG). Sie können sie jederzeit für die Zukunft widerrufen, indem Sie das Formular nicht mehr '
            .'öffnen. Von betterplace.org gesetzte Cookies können Sie in Ihrem Browser löschen.',
            'Mehr dazu in der [Datenschutzerklärung von betterplace.org](https://www.betterplace.org/c/regeln/datenschutz).',
        ],
    ];

    public const KONTAKTFORMULAR = [
        'Über die Formulare auf dieser Website, etwa auf den Seiten „Anfragen“ und „Beschwerdemanagement“, '
        .'können Sie uns schreiben. Dabei übermitteln Sie uns:',
        'Betreff und Nachricht',
        'freiwillig Ihren Namen und Ihre E-Mail-Adresse. Ohne E-Mail-Adresse können wir Ihnen nicht antworten.',
        'Ihre Nachricht speichern wir verschlüsselt in unserem Verwaltungsbereich. Per E-Mail erfahren wir nur, '
        .'dass eine neue Nachricht vorliegt, der Inhalt geht nicht per E-Mail heraus. Ihre IP-Adresse speichern '
        .'wir nicht. Gegen massenhaften Versand merkt sich der Server für zehn Minuten nur eine Prüfsumme davon.',
        'Eine Beschwerde über unseren Verein, die Sie im Beschwerdemanagement an die Ombudsstelle richten, '
        .'speichern wir nicht. Sie geht als E-Mail direkt an die Ombudsstelle.',
        'Es erfolgt keine Weitergabe an Dritte, sofern wir nicht aufgrund gesetzlicher Bestimmungen oder eines '
        .'Gerichtsbeschlusses hierzu verpflichtet sind.',
        '*Rechtsgrundlage für die Datenverarbeitung*',
        'Bei Vorliegen einer Einwilligung des Nutzers: Art. 6 Abs. 1 lit. a DSGVO. Bei Übersendung einer E-Mail: '
        .'Art. 6 Abs. 1 lit. f DSGVO (berechtigtes Interesse an Bearbeitung der Anfrage). Zielt der E-Mail-Kontakt '
        .'auf den Abschluss eines Vertrags ab, zusätzlich Art. 6 Abs. 1 lit. b DSGVO.',
        '*Zweck der Datenverarbeitung*',
        'Die Verarbeitung Ihrer personenbezogenen Daten aus der Eingabemaske dient allein zur Bearbeitung der '
        .'Kontaktaufnahme. Die Prüfsumme beim Absenden dient der Missbrauchsprävention und der Sicherheit '
        .'unserer IT-Systeme.',
        '*Dauer der Speicherung*',
        'Bearbeitete Nachrichten löschen wir automatisch 90 Tage nach Abschluss der Bearbeitung, unbearbeitete '
        .'spätestens 365 Tage nach Eingang, es sei denn, wir sind gesetzlich zu einer längeren Aufbewahrung '
        .'verpflichtet. Die Prüfsumme verfällt nach zehn Minuten.',
        '*Widerspruchs- und Beseitigungsmöglichkeit*',
        'Sie können Ihre Einwilligung zur Verarbeitung personenbezogener Daten jederzeit widerrufen und der '
        .'Speicherung Ihrer Nachricht jederzeit widersprechen. In diesem Fall kann die Konversation nicht '
        .'fortgeführt werden. Alle personenbezogenen Daten, die im Zuge der Kontaktaufnahme gespeichert wurden, '
        .'werden dann gelöscht.',
    ];

    /** KEV-226: Anmeldung zu Selbsthilfegruppen über die Website, hinter „Kontaktformular“. */
    public const ANMELDUNG = [
        'titel' => 'Anmeldung zu Selbsthilfegruppen',
        'absaetze' => [
            'Zu unseren Selbsthilfegruppen können Sie sich über das Formular auf der Seite der jeweiligen Gruppe '
            .'anmelden. Dabei übermitteln Sie uns Ihre E-Mail-Adresse und freiwillig einen Namen, ein Vorname oder '
            .'Spitzname genügt. Außerdem speichern wir, zu welcher Gruppe Sie sich angemeldet haben, und wann Sie '
            .'der Teilnahme- und Verschwiegenheitsvereinbarung, den Gruppenregeln und der Verarbeitung zugestimmt haben.',
            'Schon die Anmeldung zu einer Selbsthilfegruppe kann etwas über Ihre Gesundheit aussagen. Wir verarbeiten '
            .'diese Angaben deshalb nur mit Ihrer ausdrücklichen Einwilligung (Art. 9 Abs. 2 lit. a in Verbindung mit '
            .'Art. 6 Abs. 1 lit. a DSGVO) und nur, um Ihre Anmeldung zu bearbeiten und Ihnen den Zugang zur Gruppe zu schicken.',
            'Ihre Angaben speichern wir verschlüsselt in unserem Verwaltungsbereich. Per E-Mail erfahren wir nur, dass '
            .'eine Anmeldung vorliegt, ohne Ihren Namen und Ihre E-Mail-Adresse. Sie erhalten von uns per E-Mail eine '
            .'Bestätigung und den Zugangslink zur Gruppe. Eine Weitergabe an Dritte findet nicht statt.',
            '*Dauer der Speicherung*',
            'Bearbeitete Anmeldungen löschen wir automatisch 30 Tage nach der Freigabe oder Ablehnung, unbearbeitete '
            .'spätestens 90 Tage nach Eingang.',
            '*Widerruf*',
            'Sie können Ihre Einwilligung jederzeit mit Wirkung für die Zukunft widerrufen, etwa per E-Mail an '
            .'selbsthilfe@kein-einzelfall.de. Ihre Anmeldung wird dann gelöscht.',
        ],
    ];

    public function run(): void
    {
        foreach (Page::where('slug', self::SLUG)->where('locale', 'de')->get() as $seite) {
            self::umbauen($seite);
        }
    }

    public static function umbauen(Page $seite): void
    {
        $seite->blocks()->where('typ', 'text')->get()
            ->filter(fn ($b) => ($b->data['titel'] ?? null) === self::ALT_EINWILLIGUNGSTOOL)
            ->each->delete();

        $alt = self::altseite();

        foreach (['Cookies' => self::COOKIES, 'Kontaktformular' => self::KONTAKTFORMULAR] as $titel => $neu) {
            foreach ($seite->blocks()->where('typ', 'text')->get() as $block) {
                if (($block->data['titel'] ?? null) === $titel && isset($alt[$titel])
                    && ($block->data['absaetze'] ?? null) === $alt[$titel]) {
                    $block->update(['data' => array_replace($block->data, ['absaetze' => $neu])]);
                }
            }
        }

        $bloecke = $seite->blocks()->orderBy('position')->get()->values();
        $cookies = $bloecke->search(fn ($b) => $b->typ === 'text' && ($b->data['titel'] ?? null) === 'Cookies');
        $neue = [];

        foreach ([self::SPEICHER, self::BETTERPLACE] as $abschnitt) {
            if (! $bloecke->contains(fn ($b) => ($b->data['titel'] ?? null) === $abschnitt['titel'])) {
                $neue[] = ['typ' => 'text', 'data' => $abschnitt];
            }
        }

        // Hinter „Cookies“, sonst ans Ende. Danach durchnummerieren.
        if ($neue) {
            $davor = $cookies === false ? $bloecke : $bloecke->slice(0, $cookies + 1);
            $danach = $cookies === false ? collect() : $bloecke->slice($cookies + 1);
            $position = 0;

            foreach ($davor as $block) {
                $block->update(['position' => $position++]);
            }

            foreach ($neue as $neu) {
                $seite->blocks()->create([...$neu, 'position' => $position++]);
            }

            foreach ($danach as $block) {
                $block->update(['position' => $position++]);
            }
        }

        self::anmeldungEinfuegen($seite);

        // Die Liste selbst ans Ende: Zwischen den Abschnitten bräche sie die
        // Seitenleiste mit den Sprungmarken auf. Ohne Daten, Überschrift und
        // Einleitung kommen wie auf /barrierefreiheit aus den Übersetzungen.
        BarrierefreiheitSeeder::speicherUebersichtAnhaengen($seite);
    }

    /**
     * Absätze der Altseite, wie sie nach dem Import in der Datenbank stehen:
     * Überschrift => Absätze.
     *
     * @return array<string, list<string>>
     */
    private static function altseite(): array
    {
        $datei = base_path('docs/altseite-inhalt.json');

        if (! file_exists($datei)) {
            return [];
        }

        $bloecke = json_decode(file_get_contents($datei), true)['/'.self::SLUG.'/']['bloecke'] ?? [];

        return collect($bloecke)
            ->filter(fn ($b) => in_array($b['titel'], ['Cookies', 'Kontaktformular'], true))
            ->mapWithKeys(fn ($b) => [$b['titel'] => Textpflege::bausteinDaten(['absaetze' => $b['absaetze']], self::SLUG)['absaetze']])
            ->all();
    }

    /**
     * Den Abschnitt zur Anmeldung zu Selbsthilfegruppen (KEV-226) hinter
     * „Kontaktformular“ einfügen, sonst vor der Liste am Ende. Gibt es ihn
     * schon, passiert nichts.
     */
    public static function anmeldungEinfuegen(Page $seite): void
    {
        $bloecke = $seite->blocks()->orderBy('position')->get()->values();

        if ($bloecke->contains(fn ($b) => ($b->data['titel'] ?? null) === self::ANMELDUNG['titel'])) {
            return;
        }

        $nach = $bloecke->search(fn ($b) => $b->typ === 'text' && ($b->data['titel'] ?? null) === 'Kontaktformular');
        if ($nach === false) {
            $liste = $bloecke->search(fn ($b) => $b->typ === 'speicher_uebersicht');
            $nach = $liste === false ? $bloecke->count() - 1 : $liste - 1;
        }

        $position = 0;
        foreach ($bloecke as $i => $block) {
            $block->update(['position' => $position++]);

            if ($i === $nach) {
                $seite->blocks()->create(['typ' => 'text', 'position' => $position++, 'data' => self::ANMELDUNG]);
            }
        }

        if ($nach < 0) {
            $seite->blocks()->create(['typ' => 'text', 'position' => $position, 'data' => self::ANMELDUNG]);
        }
    }
}
