<?php

namespace App\Support;

use Illuminate\Session\DatabaseSessionHandler;

/**
 * Sitzungen in der Datenbank, aber ohne IP-Adresse und Browserkennung.
 *
 * Laravel schreibt bei jedem Seitenaufruf `ip_address` und `user_agent` in die
 * Tabelle `sessions`. Für diese Website braucht das niemand, und zusammen mit
 * dem, was nach einem Formularfehler in der Sitzung steht (der ganze
 * Nachrichtentext), wäre ein Datenbankabzug genau das Leck, das die
 * verschlüsselten Anfragen verhindern sollen (Prüfung der Firma, 07.10.2026).
 *
 * Den Inhalt verschlüsselt config/session.php (`encrypt`), dieser Handler sorgt
 * dafür, dass daneben nichts im Klartext steht. Die Spalten bleiben leer.
 */
class SitzungOhneSpuren extends DatabaseSessionHandler
{
    protected function addRequestInformation(&$payload)
    {
        return $this;
    }
}
