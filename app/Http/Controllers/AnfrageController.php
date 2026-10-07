<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnfrageRequest;
use App\Models\Inquiry;
use App\Notifications\NeueAnfrage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class AnfrageController extends Controller
{
    public function store(AnfrageRequest $request)
    {
        $anfrage = $this->annehmen($request, $request->input('herkunft'));

        return redirect(AnfrageRequest::mitSprungziel(url()->previous(), $request->input('formular')))
            ->with('versendet_von', $request->input('formular'))
            ->with('anfrage_versendet', self::bestaetigung($anfrage));
    }

    /**
     * Anfrage verschlüsselt speichern und den Verein benachrichtigen.
     * Auch für die Kritik aus dem Beschwerdeformular (BeschwerdeController).
     */
    public function annehmen(AnfrageRequest $request, ?string $herkunft): Inquiry
    {
        $anfrage = Inquiry::create([
            'name' => $request->filled('name') ? $request->string('name')->trim()->value() : null,
            'email' => $request->filled('email') ? $request->string('email')->trim()->value() : null,
            'betreff' => $request->string('betreff')->trim()->value(),
            'nachricht' => $request->string('nachricht')->trim()->value(),
            'herkunft' => $herkunft,
        ]);

        $this->benachrichtigen($anfrage);

        return $anfrage;
    }

    public static function bestaetigung(Inquiry $anfrage): string
    {
        return $anfrage->istAnonym()
            ? 'Wir haben deine Nachricht erhalten. Da du keine E-Mail-Adresse angegeben '
              .'hast, können wir dir nicht direkt antworten.'
            : 'Wir melden uns bei dir. Bitte hab etwas Geduld — wir sind ein kleines Team.';
    }

    /**
     * Den Verein informieren, dass etwas vorliegt — mehr nicht.
     *
     * Der Inhalt der Anfrage geht bewusst NICHT per E-Mail raus. E-Mail ist ein
     * unverschlüsselter Transportweg und landet in Postfächern, über die wir
     * keine Kontrolle haben. Bei Gesundheitsdaten und Angaben zu Straftaten wäre
     * das der grösste Schwachpunkt der ganzen Anwendung — und er wäre unnötig,
     * weil ein Link ins Panel denselben Zweck erfüllt.
     */
    private function benachrichtigen(Inquiry $anfrage): void
    {
        $empfaenger = config('mail.anfragen_an');

        if (blank($empfaenger)) {
            return;
        }

        try {
            Notification::route('mail', $empfaenger)->notify(new NeueAnfrage($anfrage));
        } catch (\Throwable $e) {
            // Ein Ausfall des Mailversands darf die Anfrage nicht verschlucken —
            // sie liegt bereits sicher in der Datenbank.
            Log::error('Benachrichtigung über neue Anfrage fehlgeschlagen', [
                'anfrage_id' => $anfrage->id,
                'fehler' => $e->getMessage(),
            ]);
        }
    }
}
