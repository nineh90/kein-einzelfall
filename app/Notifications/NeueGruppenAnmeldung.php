<?php

namespace App\Notifications;

use App\Models\GroupRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Hinweis an den Verein, dass eine Anmeldung zu einer Selbsthilfegruppe
 * vorliegt (KEV-226).
 *
 * Wie NeueAnfrage OHNE persönliche Angaben: kein Name, keine E-Mail-Adresse.
 * Nur welche Gruppe, wann, und ob der Link schon raus ist. Wer sich wo
 * angemeldet hat, steht verschlüsselt im Verwaltungsbereich.
 */
class NeueGruppenAnmeldung extends Notification
{
    use Queueable;

    public function __construct(private readonly GroupRegistration $anmeldung) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $gruppe = $this->anmeldung->group?->getRawOriginal('name') ?? 'eine Selbsthilfegruppe';
        $zeit = $this->anmeldung->created_at;

        $mail = (new MailMessage)
            ->subject('Neue Anmeldung: '.$gruppe)
            ->greeting('Hallo,')
            ->line('über die Website ist eine Anmeldung zur Selbsthilfegruppe „'.$gruppe.'“ eingegangen.')
            ->line('Eingegangen am '.$zeit->format('d.m.Y').' um '.$zeit->format('H:i').' Uhr.');

        $mail = $this->anmeldung->status === 'freigegeben'
            ? $mail->line('Der Zugangslink ist automatisch verschickt worden (Einstellung der Gruppe).')
            : $mail->line('Bitte gib die Anmeldung im Verwaltungsbereich frei, dann geht der Zugangslink raus.');

        return $mail
            ->action('Anmeldungen öffnen', url('/admin/group-registrations'))
            ->line('Name und E-Mail-Adresse stehen bewusst nicht in dieser E-Mail: Schon die Anmeldung '
                .'ist eine Gesundheitsangabe. Sie liegen verschlüsselt im Verwaltungsbereich.')
            ->salutation('Viele Grüße');
    }
}
