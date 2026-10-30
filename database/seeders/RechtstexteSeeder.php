<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Datenschutzerklärung und Impressum, neu geschrieben für diese Website
 * (Entwurf in docs/Entwurf-Rechtstexte-GoLive.md, 30.10.2026).
 *
 * Kevin am 30.10.2026: online stellen, Taddi liest es dort gegen. Ersetzt
 * die aus der Altseite übernommenen und mit dem DatenschutzSeeder nur
 * stellenweise angepassten Texte vollständig. Läuft beim Import
 * (AltseiteSeeder) und per Migration auf bestehenden Datenbanken.
 *
 * Kein Abschnitt wird eingeklappt („alles_zeigen“): Ein Rechtstext hinter
 * „Weiterlesen“ ist schwerer zu finden.
 */
class RechtstexteSeeder extends Seeder
{
    public const DATENSCHUTZ_SLUG = 'datenschutz';

    public const IMPRESSUM_SLUG = 'impressum';

    public const STAND = 'Stand: 30.10.2026';

    /** @var list<array{titel?: string, absaetze: list<string>}> */
    public const DATENSCHUTZ = [
        ['absaetze' => [
            'Wir nehmen den Schutz deiner Daten ernst. Diese Website ist so gebaut, dass sie so wenig wie möglich '
            .'über dich erfährt: kein Tracking, keine Werbung, keine Statistikdienste, keine Schriften oder Inhalte '
            .'von fremden Servern, solange du sie nicht selbst anklickst. Hier steht, welche Daten trotzdem anfallen, '
            .'wofür und wie lange.',
        ]],
        ['titel' => 'Wer ist verantwortlich?', 'absaetze' => [
            'KE!N EINZELFALL e.V., Schiffbeker Höhe 30, 22119 Hamburg',
            'Mobil: +49 (176) 622 08 645, E-Mail: kontakt@kein-einzelfall.de',
            'Vertreten durch den Vorstand: Tatjana Belmar und Franziska Künstler.',
            '*Unsere Datenschutzbeauftragte:* Tanja Häfner, E-Mail: haefner@kein-einzelfall.de',
            'Fragen zum Datenschutz kannst du direkt an sie richten oder an datenschutz@kein-einzelfall.de.',
        ]],
        ['titel' => 'Deine Rechte', 'absaetze' => [
            "Du hast jederzeit das Recht,\n"
            ."- *Auskunft* zu bekommen, welche Daten wir über dich speichern (Art. 15 DSGVO),\n"
            ."- falsche Daten *berichtigen* zu lassen (Art. 16 DSGVO),\n"
            ."- deine Daten *löschen* zu lassen (Art. 17 DSGVO),\n"
            ."- die Verarbeitung *einschränken* zu lassen (Art. 18 DSGVO),\n"
            ."- deine Daten in einem gängigen Format zu *erhalten* (Art. 20 DSGVO),\n"
            ."- einer Verarbeitung, die auf unserem berechtigten Interesse beruht, *zu widersprechen* (Art. 21 DSGVO),\n"
            .'- eine erteilte *Einwilligung zu widerrufen*, mit Wirkung für die Zukunft (Art. 7 Abs. 3 DSGVO).',
            'Schreib uns dafür einfach an datenschutz@kein-einzelfall.de oder an unsere Datenschutzbeauftragte '
            .'(haefner@kein-einzelfall.de).',
            'Du kannst dich außerdem bei einer Datenschutz-Aufsichtsbehörde beschweren. Für uns zuständig ist der '
            .'Hamburgische Beauftragte für Datenschutz und Informationsfreiheit, Ludwig-Erhard-Straße 22, 20459 Hamburg.',
        ]],
        ['titel' => 'Hosting und Server-Logfiles', 'absaetze' => [
            'Diese Website liegt bei ALL-INKL.COM – Neue Medien Münnich, Hauptstraße 68, 02742 Friedersdorf. Die '
            .'Server stehen in Deutschland. Mit all-inkl haben wir einen Vertrag über Auftragsverarbeitung '
            .'(Art. 28 DSGVO) geschlossen.',
            'Zugriffsprotokolle (Logfiles) mit IP-Adressen haben wir bei all-inkl abgeschaltet, ebenso '
            .'Zugriffsstatistiken. Beim Aufruf der Website verarbeitet der Server deine IP-Adresse nur, um dir die '
            .'Seite auszuliefern, und speichert sie nicht.',
            'Rechtsgrundlage ist unser berechtigtes Interesse, die Website bereitzustellen (Art. 6 Abs. 1 lit. f DSGVO).',
            'Die Verbindung zur Website ist verschlüsselt (HTTPS). Daran erkennst du es am Schloss-Symbol in der '
            .'Adresszeile deines Browsers.',
        ]],
        ['titel' => 'Cookies', 'absaetze' => [
            'Diese Website setzt nur zwei Cookies. Beide stammen von uns und sind technisch notwendig. Cookies von '
            .'Drittanbietern, für Statistik oder für Werbung setzen wir nicht. Deshalb gibt es auch keinen Cookie-Banner.',
            "- *Sitzungs-Cookie* (Name endet auf „-session“): verbindet deine Seitenaufrufe zu einer Sitzung, damit "
            .'etwa ein Formular nach einem Tippfehler mit deinem Text zurückkommt. Die Sitzung speichern wir '
            ."verschlüsselt und ohne IP-Adresse oder Browserkennung.\n"
            .'- *XSRF-TOKEN:* schützt die Formulare davor, dass fremde Websites in deinem Namen etwas absenden.',
            'Beide Cookies enthalten keine Angaben zu deiner Person und werden nach zwei Stunden ohne Seitenaufruf ungültig.',
            'Rechtsgrundlage ist § 25 Abs. 2 Nr. 2 TDDDG, weil die Cookies unbedingt erforderlich sind, um die Website '
            .'und ihre Formulare bereitzustellen, sowie Art. 6 Abs. 1 lit. f DSGVO.',
            'Du kannst Cookies in deinem Browser jederzeit löschen oder blockieren. Dann lassen sich die Formulare nicht '
            .'mehr absenden, alles andere funktioniert weiter.',
        ]],
        ['titel' => 'Einstellungen im Browser', 'absaetze' => [
            'Einige Einstellungen merkt sich die Website im Speicher deines Browsers (Local Storage, für die Dauer '
            .'eines Besuchs Session Storage), und zwar nur auf deinem Gerät. Das sind keine Cookies: Nichts davon wird '
            .'an uns übertragen, und nichts davon enthält eine Kennung, über die wir dich wiedererkennen könnten. '
            .'Gespeichert wird nur, was du selbst einstellst oder auslöst:',
            "- *Darstellung:* Schriftgröße, Kontrast, Zeilenabstand und die übrigen Einstellungen der Darstellungsleiste.\n"
            ."- *Hinweis zu belastenden Inhalten:* ob der Hinweis beim Öffnen der Seite erscheint und ob du ihn abbestellt hast.\n"
            .'- *Warnung vor einzelnen Bereichen:* vor welchen Bereichen (etwa „Raum für Ungesagtes“) du die Warnung bei '
            ."diesem Besuch schon bestätigt hast. Wird beim Schließen des Browsers vergessen.\n"
            .'- *Spendenhinweis:* wie oft du Seiten aufgerufen hast, damit der Hinweis erst nach ein paar Seiten kommt, '
            ."und bis wann er nach dem Wegklicken ausbleibt.\n"
            .'- *Wall of Shame:* wie viele Sätze du schon gelesen hast, damit es nach dem Neuladen dort weitergeht. '
            .'Nach 30 Tagen ohne Weiterlesen vergessen.',
            'Die vollständige Liste steht [am Ende dieser Seite](/datenschutz#gespeicherte-einstellungen). Dort kannst '
            .'du jeden Eintrag auch zurücksetzen. Ebenso kannst du den Speicher in den Einstellungen deines Browsers löschen.',
            'Rechtsgrundlage ist § 25 Abs. 2 Nr. 2 TDDDG, weil die Speicherung unbedingt erforderlich ist, um Funktionen '
            .'bereitzustellen, die du nutzt, sowie Art. 6 Abs. 1 lit. f DSGVO.',
        ]],
        ['titel' => 'Aufrufzähler', 'absaetze' => [
            'Für einzelne Seiten zählen wir, wie oft sie aufgerufen wurden. Gespeichert wird nur die Zahl: keine '
            .'Adresse, keine Uhrzeit, kein Cookie, nichts in deinem Browser. Daraus lässt sich über niemanden etwas ablesen.',
        ]],
        ['titel' => 'Kontakt per E-Mail', 'absaetze' => [
            'Wenn du uns eine E-Mail schreibst, liegt sie in unserem Postfach bei all-inkl (siehe „Hosting und '
            .'Server-Logfiles“). Wir nutzen deine Angaben nur, um dir zu antworten, und geben sie nicht weiter.',
            'Rechtsgrundlage ist Art. 6 Abs. 1 lit. b DSGVO, wenn deine Nachricht mit einer Mitgliedschaft oder einem '
            .'unserer Angebote zusammenhängt, sonst unser berechtigtes Interesse, deine Nachricht zu beantworten '
            .'(Art. 6 Abs. 1 lit. f DSGVO). Schreibst du uns über deine Gesundheit oder über erlebte Gewalt, '
            .'verarbeiten wir diese Angaben, weil du sie uns ausdrücklich mitteilst (Art. 9 Abs. 2 lit. a DSGVO). Du '
            .'kannst jederzeit verlangen, dass wir die E-Mails löschen.',
        ]],
        ['titel' => 'Anfrageformular', 'absaetze' => [
            "Über das Formular auf der Seite „Anfragen & Austausch“ kannst du uns schreiben. Dabei übermittelst du uns:\n"
            ."- Betreff und Nachricht,\n"
            .'- freiwillig deinen Namen und deine E-Mail-Adresse. Ohne E-Mail-Adresse können wir dir nicht antworten.',
            'Deine Nachricht speichern wir verschlüsselt in unserem Verwaltungsbereich. Per E-Mail erfahren wir nur, '
            .'dass eine neue Nachricht vorliegt, der Inhalt geht nicht per E-Mail heraus. Deine IP-Adresse speichern '
            .'wir nicht. Gegen massenhaften Versand merkt sich der Server für zehn Minuten nur eine Prüfsumme davon. '
            .'Wir geben deine Nachricht nicht an Dritte weiter, außer wir sind gesetzlich oder durch einen '
            .'Gerichtsbeschluss dazu verpflichtet.',
            'Rechtsgrundlage ist deine Einwilligung, die du beim Absenden mit dem Häkchen gibst (Art. 6 Abs. 1 lit. a '
            .'DSGVO, bei Angaben zu Gesundheit oder erlebter Gewalt Art. 9 Abs. 2 lit. a DSGVO). Die Prüfsumme dient '
            .'dem Schutz vor Missbrauch (Art. 6 Abs. 1 lit. f DSGVO).',
            '*Wie lange:* Bearbeitete Nachrichten löschen wir automatisch 90 Tage nach Abschluss der Bearbeitung, '
            .'unbearbeitete spätestens 365 Tage nach Eingang. Die Prüfsumme verfällt nach zehn Minuten.',
            '*Widerruf:* Du kannst deine Einwilligung jederzeit widerrufen, etwa per E-Mail an '
            .'kontakt@kein-einzelfall.de. Wir löschen deine Nachricht dann. Ein Gespräch können wir danach nicht fortführen.',
        ]],
        ['titel' => 'Beschwerdemanagement', 'absaetze' => [
            'Eine Beschwerde über unseren Verein, die du über das Formular auf der Seite „Beschwerdemanagement“ an die '
            .'Ombudsstelle richtest, speichern wir nicht auf der Website. Sie geht als E-Mail direkt an die '
            .'Ombudsstelle. Welche Angaben du machst, entscheidest du selbst. Du kannst dich auch anonym beschweren.',
            'Rechtsgrundlage ist deine Einwilligung beim Absenden (Art. 6 Abs. 1 lit. a DSGVO, bei Angaben zu '
            .'Gesundheit oder erlebter Gewalt Art. 9 Abs. 2 lit. a DSGVO).',
        ]],
        ['titel' => 'Anmeldung zu Selbsthilfegruppen', 'absaetze' => [
            'Zu unseren Selbsthilfegruppen kannst du dich über das Formular auf der Seite der jeweiligen Gruppe '
            .'anmelden. Dabei übermittelst du uns deine E-Mail-Adresse und freiwillig einen Namen, ein Vorname oder '
            .'Spitzname genügt. Außerdem speichern wir, zu welcher Gruppe du dich angemeldet hast und wann du der '
            .'Teilnahme- und Verschwiegenheitsvereinbarung, den Gruppenregeln und der Verarbeitung zugestimmt hast.',
            'Schon die Anmeldung zu einer Selbsthilfegruppe kann etwas über deine Gesundheit aussagen. Wir verarbeiten '
            .'diese Angaben deshalb nur mit deiner ausdrücklichen Einwilligung (Art. 9 Abs. 2 lit. a in Verbindung mit '
            .'Art. 6 Abs. 1 lit. a DSGVO) und nur, um deine Anmeldung zu bearbeiten und dir den Zugang zur Gruppe zu schicken.',
            'Deine Angaben speichern wir verschlüsselt in unserem Verwaltungsbereich. Per E-Mail erfahren wir nur, dass '
            .'eine Anmeldung vorliegt, ohne deinen Namen und deine E-Mail-Adresse. Du bekommst von uns per E-Mail eine '
            .'Bestätigung und den Zugangslink zur Gruppe. Eine Weitergabe an Dritte findet nicht statt.',
            '*Wie lange:* Bearbeitete Anmeldungen löschen wir automatisch 30 Tage nach der Freigabe oder Ablehnung, '
            .'unbearbeitete spätestens 90 Tage nach Eingang.',
            '*Widerruf:* Du kannst deine Einwilligung jederzeit widerrufen, etwa per E-Mail an '
            .'selbsthilfe@kein-einzelfall.de. Deine Anmeldung wird dann gelöscht.',
        ]],
        ['titel' => 'Treffen und Veranstaltungen über Microsoft Teams', 'absaetze' => [
            'Unsere Selbsthilfegruppen, Arbeitsgruppen und Online-Veranstaltungen finden über Microsoft Teams statt. '
            .'Wir nutzen dafür ein Konto des Vereins (Microsoft 365). Anbieter ist die Microsoft Ireland Operations '
            .'Limited, One Microsoft Place, South County Business Park, Leopardstown, Dublin 18, Irland. Mit Microsoft '
            .'besteht ein Vertrag über Auftragsverarbeitung (Art. 28 DSGVO).',
            'Wenn du teilnimmst, verarbeitet Microsoft dabei vor allem: den Namen, den du beim Beitreten angibst, Bild '
            .'und Ton, wenn du Kamera und Mikrofon einschaltest, Nachrichten im Chat, deine IP-Adresse sowie Angaben zu '
            .'Gerät und Verbindung. Ein eigenes Microsoft-Konto brauchst du nicht. Du musst nicht deinen echten Namen '
            .'angeben, und Kamera und Mikrofon können ausgeschaltet bleiben. Auch nur Zuhören ist in Ordnung.',
            'Rechtsgrundlage ist Art. 6 Abs. 1 lit. b DSGVO (die Treffen, an denen du teilnehmen möchtest). Schon die '
            .'Teilnahme an einer Selbsthilfegruppe kann etwas über deine Gesundheit verraten. Diese Angaben verarbeiten '
            .'wir auf Grundlage deiner Einwilligung, die du bei der Anmeldung gegeben hast (Art. 9 Abs. 2 lit. a DSGVO).',
            '*Aufzeichnungen:* Die Treffen der Selbsthilfegruppen zeichnen wir nie auf. Veranstaltungen und Treffen der '
            .'Arbeitsgruppen können wir aufzeichnen. Darauf weisen wir vor Beginn hin. Aufgezeichnet werden Bild, Ton '
            .'und Chat, soweit du sie einschaltest. Wenn du nicht in der Aufzeichnung erscheinen möchtest, lass Kamera '
            .'und Mikrofon aus und wähle einen anderen Namen.',
            'Wir nutzen Aufzeichnungen, um ein Protokoll zu schreiben. Wenn es passt, zeigen wir Ausschnitte bei '
            .'späteren Veranstaltungen. Bist du darin zu sehen oder zu hören, fragen wir dich vorher. Die Aufzeichnung '
            .'liegt in unserem Microsoft-365-Konto und wird dort nach 90 Tagen automatisch gelöscht. Ausschnitte, die '
            .'wir mit deiner Einwilligung zeigen, bewahren wir auf, bis du widerrufst oder wir sie nicht mehr brauchen.',
            'Rechtsgrundlage ist deine Einwilligung (Art. 6 Abs. 1 lit. a DSGVO), die du gibst, indem du nach dem '
            .'Hinweis mit eingeschalteter Kamera oder Mikrofon teilnimmst, und für das Zeigen von Ausschnitten deine '
            .'gesonderte Einwilligung. Du kannst sie jederzeit widerrufen. Dann entfernen wir deine Beiträge aus der '
            .'Aufzeichnung, soweit das möglich ist, und zeigen keine Ausschnitte mehr mit dir.',
            'Microsoft kann Daten auch in den USA verarbeiten. Microsoft ist nach dem EU-US Data Privacy Framework '
            .'zertifiziert, für das die EU-Kommission ein angemessenes Datenschutzniveau festgestellt hat (Art. 45 DSGVO).',
            'Mehr dazu in der [Datenschutzerklärung von Microsoft](https://privacy.microsoft.com/de-de/privacystatement).',
        ]],
        ['titel' => 'Spenden', 'absaetze' => [
            '*Überweisung:* Auf der Spendenseite steht unsere Bankverbindung, dazu ein QR-Code für deine Banking-App. '
            .'Dabei fallen bei uns keine Daten an, bis deine Spende auf unserem Konto eingeht. Dann verarbeiten wir die '
            .'Angaben aus der Überweisung, um die Spende zu verbuchen und auf Wunsch eine Zuwendungsbestätigung '
            .'auszustellen (Art. 6 Abs. 1 lit. b und c DSGVO).',
            '*PayPal:* Der Knopf „Bei PayPal spenden“ ist ein einfacher Link. Erst wenn du ihn anklickst, öffnet sich '
            .'die Seite von PayPal (Europe) S.à r.l. et Cie, S.C.A., 22-24 Boulevard Royal, L-2449 Luxemburg. Für '
            .'alles, was dort geschieht, ist PayPal verantwortlich.',
            '*betterplace.org:* Auf unseren Seiten zum Spenden kannst du Spendenformulare von betterplace.org anzeigen '
            .'lassen. Anbieter ist die betterplace.org gGmbH, Schlesische Straße 26, 10997 Berlin. Die Formulare werden '
            .'erst geladen, wenn du das ausdrücklich anklickst. Vorher stellt dein Browser keine Verbindung zu '
            .'betterplace.org her. Nach dem Klick lädt er das Formular direkt von betterplace.org und überträgt dabei '
            .'technisch notwendige Daten wie deine IP-Adresse. betterplace.org kann dabei eigene Cookies setzen und ist '
            .'für diese Verarbeitung selbst verantwortlich.',
            'Rechtsgrundlage ist deine Einwilligung durch den Klick (Art. 6 Abs. 1 lit. a DSGVO und § 25 Abs. 1 TDDDG). '
            .'Mehr dazu in der [Datenschutzerklärung von betterplace.org](https://www.betterplace.org/c/regeln/datenschutz).',
        ]],
        ['titel' => 'Links zu anderen Seiten', 'absaetze' => [
            'Die Website verlinkt auf unsere Auftritte bei Instagram, Facebook und TikTok und auf andere Angebote, '
            .'etwa Behörden und Beratungsstellen. Das sind einfache Links. Es werden keine Inhalte dieser Dienste in '
            .'unsere Seiten eingebunden, und es fließen keine Daten an sie, solange du den Link nicht anklickst.',
            'Der Knopf *„Notausgang“* führt dich mit einem Klick auf die Startseite von Google. Erst dann stellt dein '
            .'Browser eine Verbindung zu Google her.',
        ]],
        ['titel' => 'Schriften und Inhalte', 'absaetze' => [
            'Alle Schriften, Bilder und Programme dieser Website liegen auf unserem eigenen Server. Beim Aufruf wird '
            .'keine Verbindung zu Google Fonts oder anderen fremden Servern hergestellt.',
        ]],
        ['titel' => 'Änderungen', 'absaetze' => [
            'Wir passen diese Erklärung an, wenn sich die Website oder die Rechtslage ändert. Es gilt die Fassung, die '
            .'hier steht.',
            self::STAND,
        ]],
    ];

    /** @var list<array{titel: string, absaetze: list<string>}> */
    public const IMPRESSUM = [
        ['titel' => 'Angaben gemäß § 5 DDG', 'absaetze' => [
            'KE!N EINZELFALL e.V.', 'Schiffbeker Höhe 30', '22119 Hamburg',
        ]],
        ['titel' => 'Vertreten durch den Vorstand', 'absaetze' => [
            'Tatjana Belmar (1. Vorsitzende)', 'Franziska Künstler (2. Vorsitzende)',
        ]],
        ['titel' => 'Kontakt', 'absaetze' => [
            'E-Mail: kontakt@kein-einzelfall.de', 'Kontaktformular: [kein-einzelfall.de/anfragen](/anfragen)',
        ]],
        ['titel' => 'Registereintrag', 'absaetze' => [
            'Eingetragen im Vereinsregister.', 'Registergericht: Amtsgericht Hamburg', 'Registernummer: VR 25873',
        ]],
        ['titel' => 'Verantwortlich für den Inhalt nach § 18 Abs. 2 MStV', 'absaetze' => [
            'Tatjana Belmar', 'Schiffbeker Höhe 30', '22119 Hamburg',
        ]],
        ['titel' => 'Spendenkonto', 'absaetze' => [
            'Deutsche Skatbank', 'IBAN: DE79 8306 5408 0006 8893 10', 'BIC: GENODEF1SLR', 'PayPal: paypal@kein-einzelfall.de',
        ]],
        ['titel' => 'Bildnachweis', 'absaetze' => [
            'Titelbilder und Illustrationen: mit KI erzeugt.', 'Fotos: KE!N EINZELFALL e.V.',
        ]],
    ];

    public function run(): void
    {
        foreach (Page::where('locale', 'de')->whereIn('slug', [self::DATENSCHUTZ_SLUG, self::IMPRESSUM_SLUG])->get() as $seite) {
            self::setzen($seite);
        }
    }

    /** Alle Bausteine der Seite durch den neuen Text ersetzen. Mehrfach ausführbar. */
    public static function setzen(Page $seite): void
    {
        $abschnitte = match ($seite->slug) {
            self::DATENSCHUTZ_SLUG => self::DATENSCHUTZ,
            self::IMPRESSUM_SLUG => self::IMPRESSUM,
        };

        $seite->blocks()->delete();

        foreach ($abschnitte as $position => $abschnitt) {
            $seite->blocks()->create([
                'typ' => 'text',
                'position' => $position,
                'data' => $abschnitt + ['alles_zeigen' => true],
            ]);
        }

        // Die Liste der gespeicherten Einstellungen ans Ende, wie bisher.
        if ($seite->slug === self::DATENSCHUTZ_SLUG) {
            BarrierefreiheitSeeder::speicherUebersichtAnhaengen($seite);
        }
    }
}
