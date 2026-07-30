@php
    use App\Models\Language;
    use App\Support\Sprachfassungen;

    $sprache = Language::aktuell();

    // $page kommt aus page.blade.php. Blade reicht die Daten der Kindansicht an
    // das Layout durch — feste Seiten wie die Startseite haben keine.
    //
    // ??= statt =: Fehlerseiten setzen die Liste bewusst auf leer. hreflang auf
    // einer 404 zeigte auf Adressen, die es in keiner Sprache gibt.
    $fassungen ??= Sprachfassungen::fuer(request(), $page ?? null)->adressen();
@endphp
<!DOCTYPE html>
{{-- dir wird von Anfang an mitgeführt, obwohl DE/EN/RU alle ltr sind: für einen
     Opferhilfeverein in Hamburg sind Arabisch oder Farsi realistische spätere
     Wünsche, und nachträglich ist rtl teuer. --}}
<html lang="{{ $sprache->code }}" dir="{{ $sprache->richtung }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2E4A3A">
    <link rel="icon" href="/img/logo.png" type="image/png">

    {{-- Zwei Wege zum Titel:

         `vollertitel` setzt eine Seite, die einen gepflegten meta_title hat —
         der ersetzt den Titel vollständig, samt Suffix. Ohne ihn blieb das Feld
         im Panel wirkungslos: Der Verein konnte es ausfüllen, und im Quelltext
         änderte sich nichts. Aufgefallen ist das erst beim Nachzählen —
         alle 24 Altseiten tragen zufällig genau das Suffix, das das Layout
         ohnehin angehängt hat.

         Ohne `vollertitel` gilt weiterhin das Muster der Altseite
         („%Seite% - Kein Einzelfall e.V.“), damit sich die Suchergebnisse beim
         Umzug nicht verändern. --}}
    <title>@hasSection('vollertitel')@yield('vollertitel')@else@yield('title', 'Startseite') - Kein Einzelfall e.V.@endif</title>
    <meta name="description" content="@yield('description', 'Austausch- und Informationsplattform für Opfer und Mit-Opfer von Straftaten, Angehörige und Fachpersonen.')">

    {{-- Kanonische Adresse ohne Query-Parameter. Die Altseite setzt sie auf jeder
         Seite; ohne sie würden wir beim Umzug SEO-Substanz verlieren.

         Nicht auf Fehlerseiten: Eine kanonische Adresse, die es nicht gibt,
         ist eine Aussage über eine Seite, die es nicht gibt. --}}
    @unless ($istFehlerseite ?? false)
        <link rel="canonical" href="{{ url()->current() }}">
    @endunless

    {{-- Sprachfassungen verweisen gegenseitig aufeinander. Ohne das wertet
         Google Übersetzungen als doppelten Inhalt — und „SEO darf nicht
         schlechter werden“ ist ausdrücklicher Kundenwunsch.

         x-default zeigt auf die Standardsprache: Sie gilt für alle, deren
         Sprache wir nicht anbieten. --}}
    @if (count($fassungen) > 1)
        @foreach ($fassungen as $code => $adresse)
            <link rel="alternate" hreflang="{{ $code }}" href="{{ url($adresse) }}">
        @endforeach
        @isset($fassungen[Language::standardCode()])
            <link rel="alternate" hreflang="x-default" href="{{ url($fassungen[Language::standardCode()]) }}">
        @endisset
    @endif

    <meta property="og:type" content="website">
    <meta property="og:locale" content="{{ $sprache->ogLocale() }}">
    <meta property="og:site_name" content="KE!N EINZELFALL e.V.">
    <meta property="og:title" content="@yield('title', 'Startseite')">
    <meta property="og:description" content="@yield('description', 'Austausch- und Informationsplattform für Opfer und Mit-Opfer von Straftaten, Angehörige und Fachpersonen.')">
    <meta property="og:url" content="{{ url()->current() }}">

    {{-- Notausgang zuerst: muss auch dann funktionieren, wenn alles Weitere scheitert. --}}
    <x-layout.exit-script />

    {{-- Gespeicherte Darstellungs-Einstellungen anwenden, bevor der Browser zeichnet.
         Sonst blitzt bei jedem Seitenaufruf kurz die Standardansicht auf — für
         Menschen, die den Kontrastmodus brauchen, ist das kein Schönheitsfehler.

         Deshalb muss das hier stehen und nicht im Bundle: Das Bundle lädt erst
         nach dem ersten Zeichnen. Die Toolbar im Header greift später auf
         dieselben drei Funktionen zu, statt sie ein zweites Mal zu schreiben —
         vorher stand die Wertetabelle an beiden Stellen und konnte auseinanderlaufen. --}}
    <script @isset($cspNonce) nonce="{{ $cspNonce }}" @endisset>
    window.keDarstellung = (function () {
        var REGELN = @json(config('darstellung.anwendung'));
        var SCHLUESSEL = @json(config('darstellung.speicher'));

        function lesen() {
            try {
                return JSON.parse(localStorage.getItem(SCHLUESSEL)) || {};
            } catch (e) {
                return {};   // defekte Daten dürfen die Seite nicht mitreissen
            }
        }

        function speichern(werte) {
            try {
                localStorage.setItem(SCHLUESSEL, JSON.stringify(werte));
            } catch (e) {
                /* Privater Modus o.ä. — dann gilt die Einstellung eben nur für diese Sitzung. */
            }
        }

        /* Angefasst wird ausschliesslich <html>. Dadurch wirkt jede Einstellung
           automatisch auch auf Bausteine, die es heute noch nicht gibt. */
        function anwenden(werte) {
            var el = document.documentElement;

            for (var name in REGELN.variablen) {
                var regel = REGELN.variablen[name];
                el.style.setProperty(regel[0], regel[1][werte[name] || 0]);
            }
            REGELN.datensaetze.forEach(function (n) {
                el.dataset[n] = werte[n] || '';
            });
            REGELN.klassen.forEach(function (n) {
                el.classList.toggle('a11y-' + n, !!werte[n]);
            });
        }

        anwenden(lesen());

        return { lesen: lesen, speichern: speichern, anwenden: anwenden };
    })();
    </script>

    <link rel="preload" href="/fonts/source-serif-4-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/fraunces-latin.woff2" as="font" type="font/woff2" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="flex min-h-screen flex-col">

    <a href="#inhalt"
       class="sr-only focus:not-sr-only focus:absolute focus:left-2 focus:top-2 focus:z-50
              focus:rounded-lg focus:bg-green focus:px-4 focus:py-2 focus:text-on-green">
        {{ __('rahmen.sprunglink') }}
    </a>

    <x-layout.header :fassungen="$fassungen" />

    {{--
        Fällt eine Seite mangels Übersetzung auf die Standardsprache zurück,
        steht hier deutscher Text unter lang="ru" oder lang="en".

        Für eine Vorlesehilfe ist das nicht kosmetisch: Sie spräche den
        deutschen Text mit russischer Aussprache — unverständlich. WCAG 3.1.2
        („Sprache von Teilen“) verlangt die Auszeichnung genau dafür.
        Der Hinweis darüber bleibt in der gewählten Sprache und setzt sein
        lang-Attribut selbst.
    --}}
    {{-- data-fassung statt einer Klasse an jedem Baustein: So gelten die
         typografischen Regeln der Leichten Sprache automatisch auch fuer
         Bausteine, die es heute noch nicht gibt. Dasselbe Muster wie bei den
         Darstellungs-Einstellungen.

         Kein abweichendes lang-Attribut: Leichte Sprache *ist* Deutsch. --}}
    <main id="inhalt" tabindex="-1" class="flex-1"
          @if (($page ?? null)?->istLeichteSprache()) data-fassung="leichte-sprache" @endif
          @if (! empty($ersatzsprache))
              lang="{{ $ersatzsprache->code }}" dir="{{ $ersatzsprache->richtung }}"
          @endif>
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <x-layout.footer />
    <x-layout.mobile-bar />

    {{-- Wird nur sichtbar, wenn die Leselinie eingeschaltet ist. --}}
    <div id="leselinie" aria-hidden="true"></div>

    @stack('scripts')
</body>
</html>
