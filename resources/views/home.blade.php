@extends('layouts.app')

@section('title', 'Startseite')
@section('description', 'Austausch- und Informationsplattform für Opfer und Mit-Opfer von Straftaten, Angehörige und Fachpersonen. Selbsthilfegruppen, Wissen zum Hilfesystem und persönliche Beratung.')

@section('content')

    <x-blocks.hero
        eyebrow="Opferhilfe für soziale Gerechtigkeit"
        titel="Ein Ort zum Ankommen — für alle, die Gewalt oder Unrecht erlebt haben."
        text="Wir bieten Austausch, Information und Begleitung: für Betroffene, Mit-Betroffene, Angehörige und Fachpersonen. In deinem Tempo, so anonym du möchtest."
        hand="Gemeinsam wachsen wir weiter"
        :ctas="[
            ['label' => 'Gruppe finden', 'url' => '/selbsthilfegruppen', 'variant' => 'primary'],
            ['label' => 'Anfrage stellen', 'url' => '/anfragen', 'variant' => 'ghost'],
        ]" />

    <x-blocks.stat-strip :stats="[
        ['wert' => '1.000+', 'label' => 'erreichte Menschen'],
        ['wert' => '2024',   'label' => 'gegründet'],
        ['wert' => 'Hamburg','label' => 'Standort'],
        ['wert' => 'e.V.',   'label' => 'gemeinnützig'],
    ]" />

    <x-blocks.quick-access
        titel="Wie wir helfen"
        sub="Vier Wege, bei uns anzukommen."
        :karten="[
            ['icon' => 'users',   'titel' => 'Selbsthilfegruppen', 'text' => 'Austausch mit Menschen, die Ähnliches erlebt haben.', 'url' => '/selbsthilfegruppen', 'link' => 'Gruppe finden'],
            ['icon' => 'message', 'titel' => 'Arbeitsgruppen',     'text' => 'Gemeinsam an Strukturen arbeiten, die sich ändern müssen.', 'url' => '/arbeitsgruppen', 'link' => 'Mitmachen'],
            ['icon' => 'shield',  'titel' => 'Wissen & Hilfesystem','text' => 'Verständliche Informationen zu Anträgen, Rechten und Fristen.', 'url' => '/wissen', 'link' => 'Mehr erfahren'],
            ['icon' => 'heart',   'titel' => 'Spenden',            'text' => 'Unsere Angebote sind kostenfrei. Das geht nur gemeinsam.', 'url' => '/spenden', 'link' => 'Jetzt unterstützen'],
        ]" />

    {{-- Die Hilfe-Box steht bewusst weit oben, nicht im Footer-Bereich:
         Wer akut belastet ist, soll nicht erst die ganze Seite durchscrollen. --}}
    <div class="px-4 py-4 lg:px-10">
        <div class="mx-auto max-w-6xl">
            <x-blocks.hilfe-box titel="Du brauchst sofort jemanden zum Reden?" :kompakt="true" />
        </div>
    </div>

    <x-blocks.topic-list
        titel="Wissen & Hilfesystem"
        sub="Die Themen, zu denen uns am häufigsten Fragen erreichen."
        alleUrl="/wissen"
        alleLabel="Zum Wissensbereich"
        :themen="[
            ['label' => 'Soziales Entschädigungsrecht (OEG/SGB XIV)', 'url' => '/das-hilfesystem',                'icon' => 'shield'],
            ['label' => 'Schwerbehindertenausweis',                   'url' => '/wissen',                          'icon' => 'message'],
            ['label' => 'Erwerbsminderungsrente',                     'url' => '/erwerbsminderungsrente',          'icon' => 'message'],
            ['label' => 'FSM – Erweitertes Hilfesystem',              'url' => '/fsm-erweitertes-hilfesystem',     'icon' => 'shield'],
            ['label' => 'Istanbul-Konvention',                        'url' => '/istanbul-konvention',             'icon' => 'shield'],
            ['label' => 'Das Bürokratie-Labyrinth',                   'url' => '/buerokratie-labyrinth',           'icon' => 'message'],
        ]" />

    <x-blocks.cta-band
        eyebrow="Warum wir das tun"
        zitat="Einzelschicksale, die KE!N EINZELFALL sind — und ein System, das wir gemeinsam verändern können."
        notiz="Unsere Angebote sind kostenfrei und für Betroffene offen."
        :ctas="[
            ['label' => 'Mitglied werden', 'url' => '/mitgliedschaft', 'variant' => 'light'],
            ['label' => 'Spenden',         'url' => '/spenden',        'variant' => 'outline'],
        ]" />

    <x-blocks.contact-close
        titel="Melde dich — in deinem Tempo."
        text="Du entscheidest, was du erzählst und wann. Wenn du diese Seite schnell verlassen möchtest, ist der Notausgang immer erreichbar — oben rechts, unten in der Leiste, oder mit dreimal ESC."
        :ctas="[
            ['label' => 'Anfrage stellen', 'url' => '/anfragen', 'variant' => 'primary'],
            ['label' => 'Kontakt',         'url' => '/kontakt',  'variant' => 'ghost'],
        ]" />

@endsection
