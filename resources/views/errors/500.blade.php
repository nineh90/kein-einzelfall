{{-- Alle Fehlerseiten teilen sich eine Vorlage: gleicher Aufbau, gleiche
     Auswege, nur andere Texte. Getrennte Dateien waeren drei Stellen, an
     denen die Notfallnummern auseinanderlaufen koennen. --}}
@include('errors.fehlerseite', ['status' => 500])
