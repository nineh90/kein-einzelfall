@extends('layouts.app')

{{--
    Startseite.

    ALLE Texte sind wörtlich von https://kein-einzelfall.de/ übernommen
    (Stand 26.07.2026). Vertraglich gilt: Inhalte stellt der Verein, wir pflegen
    sie nur ein — nichts hier ist von uns formuliert.

    Die Struktur folgt dem Bestand:
      h1 KE!N EINZELFALL e.V. · Unsere Aufgabe · 4 Einstiegskarten ·
      Vereinsarbeit · Mitglieder · Spendenaufruf

    Wandert mit dem CMS in `pages` + `page_blocks`.
--}}

@section('title', 'Startseite')
@section('description', 'Austausch-/Informationsplattform für (Mit-)Opfer, Angehörige, Interessierte + Fachpersonen; zentrales Netzwerk für Sichtbarkeit + Gehör; Verein für Opferhilfe.')

@section('content')

    {{-- Steht ganz oben und nicht am Seitenende: Wer die Sprache nicht
         liest, soll es erfahren, bevor er zu lesen anfängt. --}}
    <x-layout.sprachrueckfall :quelle="$ersatzsprache ?? null" />

    <x-blocks.hero
        eyebrow="Opferhilfe für soziale Gerechtigkeit"
        titel="KE!N EINZELFALL e.V."
        text="Wir schaffen eine Austausch – und Informationsplattform für Opfer und Mit-Opfer, Angehörige, Interessierte und Fachpersonen. Ein zentrales Netzwerk aus Expertise im Betroffenenkontext, Austausch auf Augenhöhe. Wir leisten Aufklärung und geben Betroffenen eine Stimme. Für mehr Sichtbarkeit und Gehör."
        :ctas="[
            ['label' => 'Anfragen & Austausch', 'url' => '/anfragen', 'variant' => 'primary'],
            ['label' => 'Selbsthilfegruppen', 'url' => '/selbsthilfegruppen', 'variant' => 'ghost'],
        ]" />

    {{-- Neu, von Kevin beauftragt — nicht im Altbestand.
         Steht bewusst weit oben: wer akut belastet ist, soll nicht scrollen müssen. --}}
    <div class="px-4 py-6 lg:px-10">
        <div class="mx-auto max-w-6xl">
            <x-blocks.hilfe-box titel="Du brauchst sofort jemanden zum Reden?" :kompakt="true" />
        </div>
    </div>

    {{-- Die vier Einstiege der Altseite. Zwei Ziele sind dort kaputt
         (/selbsthilfegruppen-2/ und /kontaktformular/ liefern 404) —
         hier auf die richtigen Seiten korrigiert. --}}
    <x-blocks.quick-access
        titel="Unsere Aufgabe"
        sub="Keiner soll mehr sagen müssen: „Ich hab es nicht gewusst!“"
        :karten="[
            [
                'icon' => 'users',
                'titel' => 'Selbsthilfegruppen',
                'text' => 'Der Austausch in unseren Selbsthilfegruppen soll Dir genau da helfen, wo Du Hilfe benötigst, und er soll Dir aufzeigen, dass Du endlich nicht mehr alleine bist, denn wir sind KE!N EINZELFALL! Die Selbsthilfegruppen sind kostenfrei und nicht an eine Mitgliedschaft gebunden.',
                'url' => '/selbsthilfegruppen',
                'link' => 'Zu den Selbsthilfegruppen',
            ],
            [
                'icon' => 'message',
                'titel' => 'Arbeitsgruppen',
                'text' => 'Um unsere Arbeit weiter zu professionalisieren und gezielt Wirkung zu entfalten, gründen wir immer wieder Arbeitsgruppen – und laden Dich herzlich ein, Teil davon zu werden. Ein Quereinstieg ist möglich, Du kannst jederzeit dazu kommen. Ob mit Fachwissen, Kreativität oder einfach dem Wunsch, etwas zu bewegen!',
                'url' => '/arbeitsgruppen',
                'link' => 'Zu den Arbeitsgruppen',
            ],
            [
                'icon' => 'shield',
                'titel' => 'Anfragen & Austausch',
                'text' => 'Du wünschst einen persönlichen Austausch in Bezug auf das Soziale Entschädigungsrecht (OEG/SGB XIV), den Schwerbehindertenausweis und/oder den Pflegegrad, oder hast Fragen zu anderen Hilfesystemen, oder möchtest uns etwas mitteilen?',
                'url' => '/anfragen',
                'link' => 'Anfrage stellen',
            ],
            [
                'icon' => 'heart',
                'titel' => 'Spenden',
                'text' => 'Mit Deiner Spende hilfst Du uns, kostenfreies Wissen und Aufklärung zu leisten, Sichtbarkeit und Gehör zu schaffen, sowie eine Informationsplattform aufzustellen und ein Netzwerk zu bilden.',
                'url' => '/spenden',
                'link' => 'Jetzt spenden',
            ],
        ]" />

    <x-blocks.text
        titel="Vereinsarbeit"
        auf="card"
        :cta="['label' => 'Mehr über den Verein', 'url' => '/verein', 'variant' => 'primary']">
        <p>
            Der KE!N EINZELFALL e.V. wurde 2024 gegründet – aus einer persönlichen
            Betroffenheit heraus und mit dem Ziel, von schädigenden Taten betroffene
            Menschen nicht länger allein zu lassen.
        </p>
        <p class="font-hand text-2xl text-green">Opferhilfe für soziale Gerechtigkeit!</p>
    </x-blocks.text>

    <x-blocks.text
        titel="Mitglieder"
        :cta="['label' => 'Mitglied werden', 'url' => '/mitgliedschaft', 'variant' => 'primary']">
        <p>
            Jede Mitgliedschaft stärkt unsere Arbeit. Mit jeder Mitgliedschaft wächst
            unsere Chance auf Veränderung.
        </p>
        <p>
            Sei auch Du Teil unseres ständig wachsenden Netzwerks und unterstütze
            unsere Vision, indem Du Mitglied wirst.
        </p>
    </x-blocks.text>

    <x-blocks.cta-band
        eyebrow="Unterstützung"
        zitat="Sei Du dabei, jede Unterstützung zählt, egal wie gering!"
        :ctas="[
            ['label' => 'Spenden', 'url' => '/spenden', 'variant' => 'light'],
            ['label' => 'Mitglied werden', 'url' => '/mitgliedschaft', 'variant' => 'outline'],
        ]" />

    {{-- Der Text ist wörtlich aus dem Bestand. Der Zusatz zum Notausgang ist keine
         Inhaltsaussage, sondern die Erklärung einer Bedienfunktion — sonst weiß
         niemand, dass es den Tastatur-Shortcut gibt. --}}
    <x-blocks.contact-close
        titel="Anfragen & Austausch"
        text="Du wünschst einen persönlichen Austausch in Bezug auf das Soziale Entschädigungsrecht (OEG/SGB XIV), den Schwerbehindertenausweis und/oder den Pflegegrad, oder hast Fragen zu anderen Hilfesystemen, oder möchtest uns etwas mitteilen?"
        hinweis="Du kannst diese Seite jederzeit sofort verlassen: über „Notausgang“ oben rechts, in der Leiste unten, oder mit dreimal ESC."
        :ctas="[
            ['label' => 'Anfrage stellen', 'url' => '/anfragen', 'variant' => 'primary'],
            ['label' => 'Kontakt', 'url' => '/kontakt', 'variant' => 'ghost'],
        ]" />

@endsection
