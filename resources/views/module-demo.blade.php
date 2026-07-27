@extends('layouts.app')

@section('title', 'Modul-Vorschau')
@section('description', 'Interne Vorschau der Inhaltsmodule.')

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
<div class="mx-auto max-w-3xl px-4 py-10 lg:px-0">

    <p class="mb-2 text-xs uppercase tracking-[0.14em] text-green">Intern</p>
    <h1 class="mb-2 font-display text-3xl font-medium text-ink">Modul-Vorschau</h1>
    <p class="mb-10 text-ink-soft">
        Nicht Teil der Website. Dient dazu, die Bausteine mit echten Daten zu prüfen —
        auch mit Tastatur, Screenreader und in den Kontrastmodi.
    </p>

    <div class="flex flex-col gap-12">

        <section>
            <h2 class="mb-3 font-display text-sm uppercase tracking-wide text-ink-soft">hilfe_box</h2>
            <x-blocks.hilfe-box />
        </section>

        <section>
            <h2 class="mb-3 font-display text-sm uppercase tracking-wide text-ink-soft">
                hilfe_box (kompakt)
            </h2>
            <x-blocks.hilfe-box titel="Sofort jemanden erreichen" :kompakt="true" />
        </section>

        <section>
            <h2 class="mb-3 font-display text-sm uppercase tracking-wide text-ink-soft">
                inhalts_hinweis
            </h2>
            <x-blocks.inhalts-hinweis thema="Gewalt und Traumafolgen">
                <p class="text-ink-soft">
                    Beispieltext. Hier stünde der Abschnitt, vor dem gewarnt wurde.
                    Der Inhalt bleibt im Dokument und damit auch für Suchmaschinen sichtbar —
                    nur die Anzeige ist zunächst zugeklappt.
                </p>
            </x-blocks.inhalts-hinweis>
        </section>

        <section>
            <h2 class="mb-3 font-display text-sm uppercase tracking-wide text-ink-soft">
                leichte_sprache
            </h2>
            <x-blocks.leichte-sprache>
                <p>Wir sind ein Verein.</p>
                <p>Wir helfen Menschen nach einer Straftat.</p>
                <p>Bei uns kannst du mit anderen Menschen reden.<br>
                   Das kostet kein Geld.</p>
            </x-blocks.leichte-sprache>
        </section>

        <section>
            <h2 class="mb-3 font-display text-sm uppercase tracking-wide text-ink-soft">schritte</h2>
            <x-blocks.schritte
                titel="So läuft ein Antrag ab"
                einleitung="Beispieldaten zur Ansicht des Bausteins."
                :schritte="[
                    ['titel' => 'Antrag stellen', 'text' => 'Formlos beim zuständigen Amt — schriftlich und mit Datum.'],
                    ['titel' => 'Unterlagen nachreichen', 'text' => 'Atteste, Befunde und Nachweise sammeln.'],
                    ['titel' => 'Bescheid abwarten', 'text' => 'Die Bearbeitung dauert oft mehrere Monate.'],
                    ['titel' => 'Widerspruch prüfen', 'text' => 'Bei Ablehnung bleibt ein Monat Zeit.'],
                ]" />
        </section>

        <section>
            <h2 class="mb-3 font-display text-sm uppercase tracking-wide text-ink-soft">hinweis (drei Arten)</h2>
            <x-blocks.hinweis art="hinweis" text="Alle Angebote des Vereins sind kostenfrei." />
            <x-blocks.hinweis art="wichtig" titel="Kostenfrei und unabhängig"
                text="Die Selbsthilfegruppen sind nicht an eine Mitgliedschaft gebunden." />
            <x-blocks.hinweis art="frist"
                text="Gegen einen ablehnenden Bescheid kann innerhalb eines Monats Widerspruch eingelegt werden." />
        </section>

        <section>
            <h2 class="mb-3 font-display text-sm uppercase tracking-wide text-ink-soft">accordion</h2>
            <x-blocks.accordion
                titel="Häufige Fragen"
                :eintraege="[
                    ['frage' => 'Muss ich Mitglied sein, um teilzunehmen?',
                     'antwort' => '<p>Nein. Die Selbsthilfegruppen sind kostenfrei und nicht an eine Mitgliedschaft gebunden.</p>'],
                    ['frage' => 'Kann ich anonym bleiben?',
                     'antwort' => '<p>Ja. Beim Kontaktformular sind Name und E-Mail-Adresse freiwillig.</p>'],
                ]" />
        </section>

        <section>
            <h2 class="mb-3 font-display text-sm uppercase tracking-wide text-ink-soft">text_media</h2>
            <x-blocks.text-media
                titel="Wer wir sind"
                :absaetze="['Beispieltext zur Ansicht. Solange kein Foto hinterlegt ist, erscheint eine Platzhalterfläche in der Farbwelt der Seite.']" />
        </section>

        <section>
            <h2 class="mb-3 font-display text-sm uppercase tracking-wide text-ink-soft">
                download_list — echte Dokumente der Altseite
            </h2>
            <x-blocks.download-list
                titel="Erwerbsminderungsrente"
                :dokumente="$dokumente" />
        </section>

    </div>
</div>
@endsection
