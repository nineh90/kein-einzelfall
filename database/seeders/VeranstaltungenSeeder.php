<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Language;
use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * /veranstaltungen nach Taddis Doc „3. Veranstaltungen“ (KEV-317, Drive
 * „Nils Austausch Webseite“ › 2. Gruppen & Veranstaltungen ›
 * 3. Veranstaltungen). Ihr Wunsch: „auf der einen Seite ein Kalender und auf
 * der anderen Seite die nächstmöglichen Veranstaltungen“.
 *
 * Aufbau danach: Einleitung mit den Dokumenten zur Teilnahme daneben, der
 * Kalender (Raster und nächste Termine nebeneinander), dann die drei Reihen,
 * jede mit Bild, Text und ihren Terminen. Die Termine mit Datum sind
 * Veranstaltungen (pflegt der Verein in /intern), die mit nur einem Quartal
 * stehen als „geplant“ bei ihrer Reihe.
 *
 * Bewusst nicht übernommen: die Teams-Links mit Passcode aus dem Doc. Den
 * Zugangslink bekommt man nach der Anmeldung (so steht es auch im Text),
 * öffentlich widerspräche das dem (Kevin, 30.10.2026).
 *
 * Aufgerufen von der Migration (bestehende Datenbanken) und am Ende des
 * AltseiteSeeders (frische). Legt nur an, was fehlt, und baut die Seite nur
 * um, solange sie noch den Aufbau der Altseite hat.
 */
class VeranstaltungenSeeder extends Seeder
{
    public const SLUG = 'veranstaltungen';

    /** Woran der alte Aufbau zu erkennen ist: die erste Überschrift der Altseite. */
    public const ALTE_UEBERSCHRIFT = 'Wissen teilen. Erfahrungen verbinden. Gemeinsam Perspektiven schaffen.';

    private const BILD = '/img/titelbilder/';

    private const ANMELDUNG = 'veranstaltung@kein-einzelfall.de';

    private const PROGRAMM_ROSTOCK = '/dokumente/2026/11/antigewaltwoche-rostock-2026-programm.pdf';

    /** Taddis Einleitung. Fett wie im Doc, die Schlusszeile in Handschrift. */
    public const EINLEITUNG = [
        'absaetze' => [
            'Manche Themen brauchen mehr als einen kurzen Austausch. Sie brauchen Raum für Wissen, Erfahrungen, Fragen und neue Perspektiven.',
            'Mit unseren Veranstaltungen bringen wir *Betroffene, Angehörige, Interessierte und Fachpersonen* zusammen. Wir schaffen Begegnungen auf Augenhöhe, teilen Wissen und machen Themen sichtbar, über die noch viel zu selten gesprochen wird.',
            'Wir bieten Workshops, Vorträge, Dialogformate, Ausfüllhilfen und Themenveranstaltungen an – verständlich, praxisnah und auf Augenhöhe. Dabei verbinden wir Erfahrungswissen mit fachlicher Expertise und greifen Themen auf, die im Alltag oft kompliziert, belastend oder schwer zugänglich sind.',
            'Wenn es das Format zulässt, kannst du anonym teilnehmen – ohne Kamera und ohne Mikrofon. Fragen oder Beiträge kannst du dann ganz einfach über den Chat einbringen.',
            'Unsere Veranstaltungen sind kostenfrei und nicht an eine Mitgliedschaft gebunden. Du kannst zuhören, Fragen stellen, mitdenken, lernen oder dich aktiv einbringen.',
        ],
        'hand' => 'Gemeinsam KE!N EINZELFALL',
        'alles_zeigen' => true,
    ];

    /** Die drei Reihen in Taddis Reihenfolge. */
    public const REIHEN = [
        [
            'reihe' => 'dialog-wissen',
            'titel' => 'KE!N EINZELFALL im Dialog – Wissen trifft Erfahrung',
            'bild' => self::BILD.'reihe-wissen-trifft-erfahrung.webp',
            'absaetze' => [
                '*Wenn Fachwissen auf Erfahrung trifft, entstehen neue Perspektiven.*',
                'Traumatische Erfahrungen und ihre Folgen werfen viele Fragen auf. Wissenschaftliche Erkenntnisse und fachliche Einschätzungen sind dabei ebenso wertvoll wie das Wissen der Menschen, die diese Erfahrungen selbst gemacht haben.',
                'Mit unserer Veranstaltungsreihe *„KE!N EINZELFALL im Dialog – Wissen trifft Erfahrung“* bringen wir Fachwissen und Erfahrungen auf Augenhöhe zusammen. Wir möchten Zusammenhänge verständlich machen, unterschiedliche Perspektiven verbinden und durch Wissen die Selbstwirksamkeit stärken. Wir reden miteinander, nicht übereinander.',
                'Die Veranstaltungen finden kostenfrei und online über Microsoft Teams statt. Sie stehen Betroffenen, Angehörigen, Fachpersonen und allen Interessierten offen. Auch eine anonyme Teilnahme ist möglich.',
                '*Denn erst im Zusammenspiel von Fachwissen und Erfahrungen entsteht ein umfassenderes Verständnis.*',
            ],
            'geplant' => [
                [
                    'titel' => 'Trauma verstehen – Diagnosen, Therapien und neue Perspektiven',
                    'zeitraum' => '1. Quartal 2027',
                    'text' => 'Wenn Diagnosen Fragen aufwerfen und der Weg zur Hilfe zur Herausforderung wird.',
                    'bild' => self::BILD.'veranstaltung-trauma-verstehen.webp',
                ],
                [
                    'titel' => 'Gutachten – Zwischen notwendiger Begutachtung und belastenden Folgen',
                    'zeitraum' => '3. Quartal 2027',
                    'text' => 'Wenn eine Beurteilung weitreichende Folgen für das Leben Betroffener hat.',
                    'bild' => self::BILD.'veranstaltung-gutachten.webp',
                ],
            ],
        ],
        [
            'reihe' => 'dialog-frage',
            'titel' => 'KE!N EINZELFALL im Dialog – Frage trifft Antwort',
            'bild' => self::BILD.'reihe-frage-trifft-antwort.webp',
            'absaetze' => [
                'Deine Fragen. Unsere Erfahrungen. Gemeinsam Antworten finden.',
                'Manchmal fehlt nur eine Information, manchmal ist ein Zusammenhang unklar oder man weiß einfach nicht, an wen man sich mit einer Frage wenden soll.',
                'Mit *„KE!N EINZELFALL im Dialog – Frage trifft Antwort“* möchten wir einen unkomplizierten, offenen Austausch ermöglichen. Es gibt kein festgelegtes Thema, keinen Vortrag und keine vorbereiteten Fragen. Jeder kann das ansprechen, was ihn gerade beschäftigt.',
                'Ein wechselndes Team von *KE!N EINZELFALL e.V.* begleitet den Abend und versucht, die Fragen gemeinsam zu beantworten. Dabei bringen wir unser Wissen und unsere Erfahrungen ein. Und wenn wir einmal keine Antwort haben, sagen wir das offen und versuchen, sie bis zum nächsten Treffen herauszufinden.',
                'Ohne Fachchinesisch, ohne Hemmschwelle – einfach fragen, zuhören und miteinander ins Gespräch kommen. Jeden ersten Donnerstag eines Quartals von 18:30 bis 20:30 Uhr, online über Microsoft Teams und kostenfrei.',
                '*Keine Frage ist zu klein, zu unwichtig oder zu selbstverständlich.*',
            ],
            'geplant' => [],
        ],
        [
            'reihe' => 'zu-gast',
            'titel' => 'KE!N EINZELFALL zu Gast – Eingeladen, um Wissen zu teilen',
            'bild' => self::BILD.'reihe-zu-gast.webp',
            'absaetze' => [
                '*Betroffenenexpertise dort einbringen, wo sie gefragt ist.*',
                'Ob Fachkongress, Podiumsdiskussion, Workshop oder Informationsveranstaltung – *KE!N EINZELFALL e.V.* ist auf Einladung anderer Vereine, Organisationen und Institutionen zu Gast.',
                '*Wir sind selbst Betroffene.* Unsere persönlichen Erfahrungen haben wir durch Wissen, Reflexion und die praktische Arbeit in der Opferhilfe zu Betroffenenexpertise weiterentwickelt. Diese bringen wir zu den unterschiedlichsten Themen in fachliche und gesellschaftliche Diskussionen ein – überall dort, wo sie gefragt und willkommen ist.',
                'Dabei geht es uns um Austausch auf Augenhöhe, neue Perspektiven und ein besseres Verständnis für die Lebensrealitäten Betroffener.',
                '*Du möchtest uns einladen?* Du planst eine Veranstaltung, einen Fachkongress, einen Workshop oder eine Podiumsdiskussion und möchtest unsere Betroffenenexpertise einbeziehen? Dann schreib uns an veranstaltung@kein-einzelfall.de. Wir freuen uns über deine Anfrage und schauen gemeinsam, wie wir uns einbringen können.',
            ],
            'geplant' => [],
        ],
    ];

    /** Der Teil „Teilnahme und Anmeldung“, gleich bei allen Online-Terminen. */
    private const TEILNAHME = '<h2>Teilnahme und Anmeldung</h2>'
        .'<p>Eine anonyme Teilnahme ist möglich. Kamera und Mikrofon können ausgeschaltet bleiben. Fragen können vorab eingereicht oder während der Veranstaltung über den Chat gestellt werden.</p>'
        .'<p>Die Veranstaltung wird mit Bild und Ton aufgezeichnet. Bitte beachte vor deiner Anmeldung unsere <a href="/veranstaltungen#dl-dokumente-zum-herunterladen">Teilnahmevereinbarung mit Verschwiegenheitserklärung und die Gruppenregeln</a>. Mit deiner Anmeldung erkennst du diese an.</p>'
        .'<p>Nach deiner Anmeldung erhältst du den Zugangslink zur Veranstaltung.</p>';

    /**
     * Die Termine mit Datum aus dem Doc. Uhrzeiten wie dort, beim
     * Bürokratie-Labyrinth fehlt sie: wie die übrigen der Reihe 18 bis 20 Uhr
     * (Rückfrage an den Verein). Die beiden Rostocker Termine nach dem Flyer
     * der Antigewaltwoche (Vortrag im Rathaus, Workshop in der Ökovilla).
     *
     * @return list<array<string, mixed>>
     */
    public static function termine(): array
    {
        $online = ['online' => true, 'ort' => 'Online über Microsoft Teams'];
        $anmelden = fn (string $was) => 'mailto:'.self::ANMELDUNG.'?subject='.rawurlencode('Anmeldung: '.$was);

        $frage = fn (string $datum) => [
            'slug' => 'frage-trifft-antwort-'.$datum,
            'reihe' => 'dialog-frage',
            'titel' => 'Frage trifft Antwort',
            'art' => 'Offener Austausch',
            'teaser' => 'Deine Fragen. Unsere Erfahrungen. Gemeinsam Antworten finden.',
            'titelbild' => self::BILD.'reihe-frage-trifft-antwort.webp',
            'beginn' => $datum.' 18:30', 'ende' => $datum.' 20:30',
            'beschreibung' => '<p>Manchmal fehlt nur eine Information, manchmal ist ein Zusammenhang unklar oder man weiß einfach nicht, an wen man sich mit einer Frage wenden soll.</p>'
                .'<p>Bei <strong>„KE!N EINZELFALL im Dialog – Frage trifft Antwort“</strong> gibt es kein festgelegtes Thema, keinen Vortrag und keine vorbereiteten Fragen. Jeder kann das ansprechen, was ihn gerade beschäftigt. Ein wechselndes Team von <strong>KE!N EINZELFALL e.V.</strong> begleitet den Abend und versucht, die Fragen gemeinsam zu beantworten. Und wenn wir einmal keine Antwort haben, sagen wir das offen und versuchen, sie bis zum nächsten Treffen herauszufinden.</p>'
                .'<p>Ohne Fachchinesisch, ohne Hemmschwelle – einfach fragen, zuhören und miteinander ins Gespräch kommen. Auch einfach nur zuzuhören ist völlig in Ordnung. Fragen kannst du auch vorab per E-Mail an <a href="mailto:'.self::ANMELDUNG.'">'.self::ANMELDUNG.'</a> stellen.</p>'
                .'<p><strong>Keine Frage ist zu klein, zu unwichtig oder zu selbstverständlich.</strong></p>'
                .self::TEILNAHME,
            'anmeldung_url' => $anmelden('Frage trifft Antwort am '.Carbon::parse($datum)->format('d.m.Y')),
        ] + $online;

        return [
            [
                'slug' => 'das-hilfesystem-2026-11-25',
                'reihe' => 'dialog-wissen',
                'titel' => 'Das Hilfesystem – Unterstützung, Selbstfürsorge und Resilienz',
                'art' => 'Vortrag und Dialog',
                'teaser' => 'Hilfe finden. Eigene Ressourcen stärken. Neue Perspektiven entdecken.',
                'titelbild' => self::BILD.'veranstaltung-das-hilfesystem.webp',
                'beginn' => '2026-11-25 18:00', 'ende' => '2026-11-25 20:00',
                'beschreibung' => '<p>Wer hilft eigentlich, wenn das Leben aus dem Gleichgewicht gerät? Welche Unterstützung gibt es und warum wird der Weg dorthin manchmal selbst zur Herausforderung?</p>'
                    .'<p>Nach traumatischen Erfahrungen stehen viele Betroffene und Angehörige vor der Aufgabe, sich im Hilfesystem zurechtzufinden. Dabei geraten die eigenen Bedürfnisse und Ressourcen häufig in den Hintergrund. Doch Selbstfürsorge, gegenseitige Unterstützung und verlässliche Netzwerke können entscheidend dazu beitragen, Stabilität und Selbstwirksamkeit zu stärken.</p>'
                    .'<p>Wir freuen uns, Quendolin Winter, Autorin des Buches „Das Helfernetzwerk“, als Referentin begrüßen zu dürfen. Gemeinsam möchten wir beleuchten, wie hilfreiche Netzwerke entstehen, welche Rolle Angehörige spielen und wie Selbstfürsorge und Resilienz im Alltag gestärkt werden können.</p>'
                    .'<p>Im anschließenden Dialog verbinden wir Fachwissen mit Betroffenenexpertise. Dabei ist Raum für Fragen, unterschiedliche Erfahrungen und neue Perspektiven.</p>'
                    .'<h2>Die Veranstaltung auf einen Blick</h2>'
                    .'<ul><li>Referentin: Quendolin Winter</li><li>Teilnahme: kostenfrei</li><li>Zielgruppe: Betroffene, Angehörige, Fachpersonen und alle Interessierten</li></ul>'
                    .self::TEILNAHME
                    .'<p>Wir freuen uns auf einen gemeinsamen Abend, der Wissen vermittelt, Erfahrungen verbindet und neue Wege aufzeigt.</p>',
                'anmeldung_url' => $anmelden('Das Hilfesystem am 25.11.2026'),
            ] + $online,
            [
                'slug' => 'das-buerokratie-labyrinth-2027-04-28',
                'reihe' => 'dialog-wissen',
                'titel' => 'Das Bürokratie-Labyrinth – Wenn Hilfe zur Herausforderung wird',
                'art' => 'Vortrag und Dialog',
                'teaser' => 'Wenn der Weg zur Unterstützung selbst zur Belastung wird.',
                'titelbild' => self::BILD.'veranstaltung-buerokratie-labyrinth.webp',
                'beginn' => '2027-04-28 18:00', 'ende' => '2027-04-28 20:00',
                'beschreibung' => '<p>Anträge, Gutachten, Zuständigkeiten, Widersprüche und lange Bearbeitungszeiten: Für viele Betroffene und Angehörige wird die Suche nach Unterstützung zu einer zusätzlichen Herausforderung. Gerade nach traumatischen Erfahrungen können bürokratische Hürden nicht nur überfordern, sondern bestehende Belastungen weiter verstärken.</p>'
                    .'<p>In unserer Veranstaltungsreihe <strong>„KE!N EINZELFALL im Dialog – Wissen trifft Erfahrung“</strong> möchten wir gemeinsam mit Tatjana Belmar, 1. Vorsitzende von KE!N EINZELFALL e.V. – Opferhilfe für soziale Gerechtigkeit, beleuchten, wo Hilfesysteme an den Bedürfnissen Betroffener vorbeigehen und wie Unterstützung verständlicher, zugänglicher und traumasensibler gestaltet werden kann.</p>'
                    .'<p>Dabei verbinden wir sozialrechtliches Wissen, praktische Erfahrungen aus der Opferhilfe und Betroffenenexpertise. Gemeinsam möchten wir bürokratische Hürden sichtbar machen, Zusammenhänge verständlich erklären und Verbesserungsmöglichkeiten aufzeigen.</p>'
                    .'<p><strong>Unser Ziel: Hürden erkennen, Wissen stärken und Veränderungen anstoßen.</strong></p>'
                    .'<h2>Die Veranstaltung auf einen Blick</h2>'
                    .'<ul><li>Referentin: Tatjana Belmar</li><li>Teilnahme: kostenfrei</li><li>Zielgruppe: Betroffene, Angehörige, Fachpersonen und alle Interessierten</li></ul>'
                    .self::TEILNAHME
                    .'<p>Wir freuen uns auf einen gemeinsamen Abend, der Wissen vermittelt, Erfahrungen verbindet und neue Wege aufzeigt.</p>',
                'anmeldung_url' => $anmelden('Das Bürokratie-Labyrinth am 28.04.2027'),
            ] + $online,
            $frage('2027-01-07'),
            $frage('2027-04-01'),
            $frage('2027-07-01'),
            $frage('2027-10-07'),
            [
                'slug' => 'schweigen-war-nie-meine-wahl-2026-11-23',
                'reihe' => 'zu-gast',
                'titel' => 'Schweigen war nie meine Wahl – von der Erfahrung zur Betroffenenexpertise',
                'art' => 'Vortrag',
                'teaser' => 'Vortrag von Tatjana Belmar beim Fachsymposium der Antigewaltwoche in Rostock.',
                'titelbild' => self::BILD.'reihe-zu-gast.webp',
                'beginn' => '2026-11-23 14:30', 'ende' => '2026-11-23 15:45',
                'online' => false, 'ort' => 'Festsaal im Rathaus Rostock', 'adresse' => 'Neuer Markt 1, 18055 Rostock',
                'beschreibung' => '<p>Tatjana Belmar, 1. Vorsitzende von KE!N EINZELFALL e.V., hält einen Vortrag anlässlich der Antigewaltwoche 2026 im Rahmen der Ausstellung „Wo Worte enden, spricht Kunst.“ Der Vortrag ist Teil des Fachsymposiums „Ausstieg aus organisierten rituellen Gewaltstrukturen“.</p>'
                    .'<p>Veranstalter sind das Traumakompetenzzentrum MV (TKz) und die Allgemeine Opferhilfeberatungsstelle im Landgerichtsbezirk Stralsund mit Schwerpunkt Organisierte/Rituelle Gewalt und Ausstiegsbegleitung (OhBst).</p>'
                    .'<p>Die Anmeldung und der Teilnahmebeitrag laufen über die Veranstalter. Alles dazu steht im <a href="'.self::PROGRAMM_ROSTOCK.'">Programm der Antigewaltwoche (PDF)</a>.</p>',
                'anmeldung_hinweis' => 'Anmeldung über die Veranstalter, siehe Programm.',
            ],
            [
                'slug' => 'wenn-aus-erfahrung-wissen-wird-2026-11-24',
                'reihe' => 'zu-gast',
                'titel' => 'Wenn aus Erfahrung Wissen wird',
                'art' => 'Workshop',
                'teaser' => 'Workshop von Tatjana Belmar bei der Antigewaltwoche in Rostock.',
                'titelbild' => self::BILD.'reihe-zu-gast.webp',
                'beginn' => '2026-11-24 12:00', 'ende' => '2026-11-24 14:00',
                'online' => false, 'ort' => 'Ökovilla, großer Seminarraum', 'adresse' => 'Hermannstraße 36, 18055 Rostock',
                'beschreibung' => '<p>Tatjana Belmar, 1. Vorsitzende von KE!N EINZELFALL e.V., gibt einen Workshop anlässlich der Antigewaltwoche 2026 im Rahmen der Ausstellung „Wo Worte enden, spricht Kunst.“</p>'
                    .'<p>Veranstalter sind das Traumakompetenzzentrum MV (TKz) und die Allgemeine Opferhilfeberatungsstelle im Landgerichtsbezirk Stralsund mit Schwerpunkt Organisierte/Rituelle Gewalt und Ausstiegsbegleitung (OhBst).</p>'
                    .'<p>Die Anmeldung und der Teilnahmebeitrag laufen über die Veranstalter. Alles dazu steht im <a href="'.self::PROGRAMM_ROSTOCK.'">Programm der Antigewaltwoche (PDF)</a>.</p>',
                'anmeldung_hinweis' => 'Anmeldung über die Veranstalter, siehe Programm.',
            ],
        ];
    }

    public function run(): void
    {
        self::anlegen();
    }

    public static function anlegen(): void
    {
        self::termineAnlegen();
        self::seiteUmbauen();
    }

    /** Nur fehlende anlegen; was der Verein geändert oder gelöscht hat, bleibt. */
    public static function termineAnlegen(): void
    {
        foreach (self::termine() as $t) {
            if (Event::where('slug', $t['slug'])->exists()) {
                continue;
            }

            Event::create([
                'slug' => $t['slug'],
                'titel' => $t['titel'],
                'teaser' => $t['teaser'],
                'beschreibung' => $t['beschreibung'],
                'titelbild' => $t['titelbild'],
                'reihe' => $t['reihe'],
                'art' => $t['art'],
                'beginnt_am' => Carbon::parse($t['beginn'], 'Europe/Berlin'),
                'endet_am' => Carbon::parse($t['ende'], 'Europe/Berlin'),
                'ganztaegig' => false,
                'online' => $t['online'],
                'ort' => $t['ort'],
                'adresse' => $t['adresse'] ?? null,
                'anmeldung_url' => $t['anmeldung_url'] ?? null,
                'anmeldung_hinweis' => $t['anmeldung_hinweis'] ?? null,
                'published_at' => now(),
            ]);
        }
    }

    /**
     * Die Bausteine der Altseite durch den neuen Aufbau ersetzen. Die
     * Dokumentenliste (Teilnahmevereinbarung, Gruppenregeln) bleibt und
     * steht neben der Einleitung.
     */
    public static function seiteUmbauen(): void
    {
        $seite = Page::query()
            ->where('slug', self::SLUG)
            ->where('locale', Language::standardCode())
            ->where('fassung', Page::FASSUNG_STANDARD)
            ->first();

        $erster = $seite?->blocks()->orderBy('position')->first();

        if (! $erster || ($erster->data['titel'] ?? null) !== self::ALTE_UEBERSCHRIFT) {
            return;
        }

        $dokumente = $seite->blocks()->where('typ', 'download_list')->first()?->data;

        $neu = [
            ['typ' => 'text', 'data' => self::EINLEITUNG],
            ...($dokumente ? [['typ' => 'download_list', 'data' => array_replace($dokumente, ['neben_text' => true])]] : []),
            // Ohne die Links darunter: „Alle Veranstaltungen“ zeigte auf
            // diese Seite selbst, das Kalender-Abo steht in der Leiste unten.
            ['typ' => 'kalender', 'data' => ['titel' => 'Termine', 'links' => false]],
            ...array_map(fn ($r) => ['typ' => 'veranstaltungsreihe', 'data' => $r], self::REIHEN),
        ];

        $seite->blocks()->delete();

        foreach ($neu as $position => $block) {
            $seite->blocks()->create($block + ['position' => $position]);
        }
    }
}
