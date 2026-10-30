{{-- Hinweis an den Verein auf eine neue Gruppenanmeldung (KEV-226).
     Reiner Text wie die Mails an die Besucherinnen, ohne Laravels
     Standardvorlage (Durchlauf 30.10.2026). Ohne Name und Adresse. --}}
Hallo,

über die Website ist eine Anmeldung zur Selbsthilfegruppe „{!! $gruppe !!}“ eingegangen, am {!! $zeit->format('d.m.Y') !!} um {!! $zeit->format('H:i') !!} Uhr.

@if ($freigegeben)
Der Zugangslink ist schon automatisch verschickt worden (Einstellung der Gruppe). Ihr müsst nichts tun.
@else
So gebt ihr sie frei:
1. Verwaltungsbereich öffnen: {!! $url !!}
2. Bei der Anmeldung auf „Freigeben und Link senden“ klicken.
3. „Bestätigen“ klicken. Die Person bekommt den Teams-Link per E-Mail.
@endif

Name und E-Mail-Adresse stehen bewusst nicht in dieser E-Mail: Schon die Anmeldung ist eine Gesundheitsangabe. Sie liegen verschlüsselt im Verwaltungsbereich.

Viele Grüße
Eure Website
