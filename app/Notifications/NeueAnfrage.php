<?php

namespace App\Notifications;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Hinweis an den Verein, dass eine Anfrage vorliegt.
 *
 * WICHTIG: Diese E-Mail enthält KEINEN Inhalt der Anfrage — weder Name noch
 * E-Mail-Adresse, Betreff oder Nachricht. Nur die Eingangszeit und einen Link
 * ins geschützte Panel.
 *
 * Grund: E-Mail wird unverschlüsselt übertragen, liegt bei Mailanbietern und
 * bleibt oft jahrelang in Postfächern liegen. Bei Angaben zu Straftaten und
 * Gesundheit wäre das das grösste Leck der ganzen Anwendung. Der Umweg über
 * das Panel kostet einen Klick und schliesst es.
 *
 * Beim Erweitern dieser Klasse bitte dabei bleiben.
 */
class NeueAnfrage extends Notification
{
    use Queueable;

    public function __construct(private readonly Inquiry $anfrage) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Reiner Text wie die Mails an die Besucherinnen, ohne Laravels
        // Standardvorlage (Durchlauf 30.10.2026).
        return (new MailMessage)
            ->subject('Neue Anfrage über die Website')
            ->view(['text' => 'mail.hinweis-neue-anfrage'], [
                'zeit' => $this->anfrage->created_at,
                'url' => url('/admin/inquiries/'.$this->anfrage->id.'/edit'),
            ]);
    }
}
