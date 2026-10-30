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

        // Reiner Text wie die Mails an die Besucherinnen. Laravels Vorlage
        // zeigte oben „KEIN EINZELFALL: http://…“ und unten „Alle Rechte
        // vorbehalten“ (Durchlauf 30.10.2026).
        return (new MailMessage)
            ->subject('Neue Anmeldung: '.$gruppe)
            ->view(['text' => 'mail.hinweis-neue-anmeldung'], [
                'gruppe' => $gruppe,
                'zeit' => $zeit,
                'freigegeben' => $this->anmeldung->status === 'freigegeben',
                'url' => url('/admin/group-registrations'),
            ]);
    }
}
