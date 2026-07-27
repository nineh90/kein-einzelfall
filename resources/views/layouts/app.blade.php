<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2E4A3A">
    <link rel="icon" href="/img/logo.png" type="image/png">

    <title>@yield('title', 'Startseite') - Kein Einzelfall e.V.</title>
    <meta name="description" content="@yield('description', 'Austausch- und Informationsplattform für Opfer und Mit-Opfer von Straftaten, Angehörige und Fachpersonen.')">

    {{-- Kanonische Adresse ohne Query-Parameter. Die Altseite setzt sie auf jeder
         Seite; ohne sie würden wir beim Umzug SEO-Substanz verlieren. --}}
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="de_DE">
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
        Zum Inhalt springen
    </a>

    <x-layout.header />

    <main id="inhalt" tabindex="-1" class="flex-1">
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
