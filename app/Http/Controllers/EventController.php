<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Group;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EventController extends Controller
{
    public function index(Request $request)
    {
        // Vergangene Termine bleiben erreichbar — sie zeigen, dass der Verein
        // aktiv ist, und sind für Suchmaschinen weiterhin sinnvoller Inhalt.
        $zeigeVergangene = $request->string('zeitraum')->value() === 'vergangen';

        $termine = Event::veroeffentlicht()
            ->when($zeigeVergangene,
                fn ($q) => $q->vergangen()->latest('beginnt_am'),
                fn ($q) => $q->kommend()->oldest('beginnt_am'))
            ->paginate(12)
            ->withQueryString();

        return view('events.index', [
            'termine' => $termine,
            'zeigeVergangene' => $zeigeVergangene,
            'anzahlKommend' => Event::veroeffentlicht()->kommend()->count(),
            'anzahlVergangen' => Event::veroeffentlicht()->vergangen()->count(),

            // /veranstaltungen ist auf der Altseite eine gepflegte Inhaltsseite
            // (443 Wörter). Der Text bleibt als Einleitung erhalten, der
            // Kalender kommt darunter — statt den Bestand zu verwerfen.
            'einleitung' => Page::veroeffentlicht()
                ->with('blocks')
                ->where('slug', 'veranstaltungen')
                ->first(),

            // Die regelmäßigen Gruppentreffen gehören in denselben Kalender.
            // Vorher standen sie nur auf den Gruppenseiten — wer wissen wollte,
            // wann das nächste Treffen ist, musste die Seite durchsuchen.
            'gruppentermine' => $zeigeVergangene ? collect() : $this->gruppentermine(),
        ]);
    }

    public function show(string $slug)
    {
        $termin = Event::veroeffentlicht()->where('slug', $slug)->firstOrFail();

        return view('events.show', compact('termin'));
    }

    /**
     * Die nächsten Treffen aller Gruppen mit festem Rhythmus.
     *
     * Berechnet statt gespeichert — ein monatlicher Termin würde sonst
     * unbegrenzt Datensätze erzeugen. Nach Datum sortiert, damit sie sich mit
     * den Einzelveranstaltungen zu einer Liste mischen lassen.
     */
    private function gruppentermine()
    {
        return Group::veroeffentlicht()
            ->where('status', 'offen')
            ->whereNot('wiederholung', 'keine')
            ->orderBy('name')
            ->get()
            ->flatMap(fn (Group $gruppe) => $gruppe->naechsteTermine(2)
                ->map(fn ($zeitpunkt) => [
                    'gruppe' => $gruppe,
                    'zeitpunkt' => $zeitpunkt,
                ]))
            ->sortBy('zeitpunkt')
            ->values();
    }

    /**
     * iCal-Export für den gesamten Kalender.
     *
     * Die Altseite bietet das unter /events/?ical=1 an — die Möglichkeit soll
     * nicht verloren gehen. Wer Termine im eigenen Kalender führt, muss sie
     * nicht abtippen.
     */
    public function ical(): Response
    {
        $termine = Event::veroeffentlicht()->kommend()->oldest('beginnt_am')->get();

        // Die regelmässigen Gruppentreffen gehören mit in den Export — sonst
        // fehlt im eigenen Kalender genau das, was am häufigsten stattfindet.
        return response($this->icalInhalt($termine, $this->gruppentermine()), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="kein-einzelfall-termine.ics"',
        ]);
    }

    /** Einzelner Termin als Kalendereintrag. */
    public function icalEinzeln(string $slug): Response
    {
        $termin = Event::veroeffentlicht()->where('slug', $slug)->firstOrFail();

        return response($this->icalInhalt(collect([$termin])), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$termin->slug.'.ics"',
        ]);
    }

    private function icalInhalt($termine, $gruppentermine = null): string
    {
        $zeilen = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//KE!N EINZELFALL e.V.//Termine//DE',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:KE!N EINZELFALL e.V.',
            'X-WR-TIMEZONE:Europe/Berlin',
        ];

        foreach ($termine as $termin) {
            $zeilen = array_merge($zeilen, $this->vevent($termin));
        }

        foreach ($gruppentermine ?? [] as $eintrag) {
            $zeilen = array_merge($zeilen, $this->veventGruppe(
                $eintrag['gruppe'],
                $eintrag['zeitpunkt']
            ));
        }

        $zeilen[] = 'END:VCALENDAR';

        // iCal schreibt CRLF vor — manche Kalenderprogramme sind da streng.
        return implode("\r\n", $zeilen)."\r\n";
    }

    private function vevent(Event $termin): array
    {
        $ende = $termin->endet_am ?? $termin->beginnt_am->copy()->addHours(2);

        $zeilen = [
            'BEGIN:VEVENT',
            'UID:termin-'.$termin->id.'@kein-einzelfall.de',
            'DTSTAMP:'.$termin->updated_at->utc()->format('Ymd\THis\Z'),
        ];

        if ($termin->ganztaegig) {
            // Ganztägig wird als reines Datum ausgegeben; DTEND ist exklusiv,
            // deshalb einen Tag weiter.
            $zeilen[] = 'DTSTART;VALUE=DATE:'.$termin->beginnt_am->format('Ymd');
            $zeilen[] = 'DTEND;VALUE=DATE:'.$ende->copy()->addDay()->format('Ymd');
        } else {
            $zeilen[] = 'DTSTART:'.$termin->beginnt_am->utc()->format('Ymd\THis\Z');
            $zeilen[] = 'DTEND:'.$ende->utc()->format('Ymd\THis\Z');
        }

        $zeilen[] = 'SUMMARY:'.$this->maskieren($termin->titel);

        if ($termin->teaser || $termin->beschreibung) {
            $zeilen[] = 'DESCRIPTION:'.$this->maskieren(
                strip_tags($termin->teaser ?: $termin->beschreibung)
            );
        }

        $ort = $termin->online ? 'Online' : trim($termin->ort.' '.$termin->adresse);
        if ($ort !== '') {
            $zeilen[] = 'LOCATION:'.$this->maskieren($ort);
        }

        $zeilen[] = 'URL:'.route('events.show', $termin->slug);
        $zeilen[] = 'END:VEVENT';

        return $zeilen;
    }

    /**
     * Ein berechnetes Gruppentreffen als Kalendereintrag.
     *
     * Die Kennung enthält das Datum, weil es keinen Datensatz je Termin gibt.
     * Dadurch erkennt das Kalenderprogramm jedes Treffen als eigenen Eintrag
     * und legt beim erneuten Import keine Dubletten an.
     */
    private function veventGruppe(Group $gruppe, $beginn): array
    {
        $ende = $beginn->addMinutes($gruppe->dauer_minuten ?: 120);

        $zeilen = [
            'BEGIN:VEVENT',
            'UID:gruppe-'.$gruppe->id.'-'.$beginn->format('Ymd').'@kein-einzelfall.de',
            'DTSTAMP:'.$gruppe->updated_at->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$beginn->utc()->format('Ymd\THis\Z'),
            'DTEND:'.$ende->utc()->format('Ymd\THis\Z'),
            'SUMMARY:'.$this->maskieren($gruppe->name),
        ];

        if ($gruppe->teaser) {
            $zeilen[] = 'DESCRIPTION:'.$this->maskieren(strip_tags($gruppe->teaser));
        }

        $ort = $gruppe->online ? ($gruppe->ort ?: 'Online') : $gruppe->ort;
        if ($ort) {
            $zeilen[] = 'LOCATION:'.$this->maskieren($ort);
        }

        $zeilen[] = 'URL:'.url('/selbsthilfegruppen');
        $zeilen[] = 'END:VEVENT';

        return $zeilen;
    }

    /** Sonderzeichen nach RFC 5545 maskieren, sonst zerfällt die Datei. */
    private function maskieren(string $text): string
    {
        $text = str_replace(['\\', ';', ','], ['\\\\', '\;', '\\,'], $text);

        return str_replace(["\r\n", "\n", "\r"], '\\n', $text);
    }
}
