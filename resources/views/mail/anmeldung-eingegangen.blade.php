{{-- Bestätigung der Anmeldung (KEV-226). Reiner Text: {!! !!} statt {{ }}. --}}
{!! $name ? __('Hallo :name,', ['name' => $name]) : __('Hallo,') !!}

{!! __('danke für deine Anmeldung zur Selbsthilfegruppe „:gruppe“. Sie ist bei uns angekommen.', ['gruppe' => $gruppe->name]) !!}

{!! __('Wir schauen sie uns an und schicken dir den Zugangslink, sobald wir sie bestätigt haben. Das kann ein paar Tage dauern, wir sind ein ehrenamtliches Team.') !!}

@include('mail._gruppe-termin', ['gruppe' => $gruppe])

{!! __('Teilnahme- und Verschwiegenheitsvereinbarung und Gruppenregeln:') !!} {!! sprachlink('page', ['slug' => 'selbsthilfegruppen']) !!}#dl-dokumente-zum-herunterladen

{!! __('Du hast es dir anders überlegt oder eine Frage? Schreib uns an :adresse.', ['adresse' => \App\Models\Group::SHG_ADRESSE]) !!}

{!! __('Herzliche Grüße') !!}
{!! __('Das Team von KE!N EINZELFALL e.V.') !!}

--
{!! __('Diese E-Mail kommt von kein-einzelfall.de, weil sich jemand mit dieser Adresse zu einer Selbsthilfegruppe angemeldet hat. Warst du das nicht, kannst du sie einfach löschen.') !!}
