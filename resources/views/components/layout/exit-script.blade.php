{{--
    Notausgang-Verhalten.

    Bewusst als Inline-Script ganz oben im <head> und ohne jede Abhängigkeit:
    weder Alpine noch das Vite-Bundle dürfen hier im Weg stehen. Wenn JavaScript
    ausfällt, bleibt der Button ein normaler Link und funktioniert trotzdem —
    genau das kann die Altseite nicht, dort wird der Button per JS erzeugt.

    Was das hier leistet:
      - location.replace() statt href → der aktuelle History-Eintrag wird ersetzt
      - 3× ESC als Tastatur-Shortcut
      - rel="noreferrer" am Link → die Zielseite erfährt nicht, woher der Besuch kam

    Was es NICHT leistet (und was wir niemandem versprechen dürfen):
      Der Browser-Verlauf lässt sich aus einer Webseite heraus nicht löschen.
      replace() entfernt nur den aktuellen Eintrag, alles davor bleibt stehen.
      Deshalb steht auf /barrierefreiheit ein Hinweis auf privates Fenster und
      Verlauf löschen. Alles andere wäre ein Sicherheitsversprechen ohne Deckung.

    Hinweis für später: Sobald eine CSP steht, braucht dieses Script ein nonce.
--}}
<script>
(function () {
    var ZIEL = @json(config('navigation.exit_url'));

    function verlassen() {
        try {
            // Neuer Tab mit neutralem Inhalt, damit der Bildschirm sofort unverfänglich ist.
            window.open(ZIEL, '_blank', 'noopener,noreferrer');
        } catch (e) { /* Popup-Blocker: egal, der Redirect unten greift trotzdem */ }

        window.location.replace(ZIEL);
    }

    window.keNotausgang = verlassen;

    // 3× ESC innerhalb von 1,5 s. Einmal ESC wäre zu leicht versehentlich ausgelöst,
    // dreimal ist bewusst genug und trotzdem in Panik schaffbar.
    var schritte = 0, timer = null;
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        schritte++;
        clearTimeout(timer);
        timer = setTimeout(function () { schritte = 0; }, 1500);
        if (schritte >= 3) verlassen();
    });

    document.addEventListener('click', function (e) {
        var el = e.target.closest('[data-notausgang]');
        if (!el) return;
        e.preventDefault();
        verlassen();
    });
})();
</script>
