<?php

namespace App\Providers;

use App\Models\Language;
use App\Support\SitzungOhneSpuren;
use App\Support\Triggerwarnung;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->triggerwarnungBereitstellen();
        $this->sitzungenOhneSpuren();
    }

    /**
     * Ersetzt den eingebauten Treiber „database“: gleiche Tabelle, aber ohne
     * IP-Adresse und Browserkennung. Begründung in SitzungOhneSpuren.
     */
    private function sitzungenOhneSpuren(): void
    {
        Session::extend('database', fn ($app) => new SitzungOhneSpuren(
            $app['db']->connection($app['config']['session.connection']),
            $app['config']['session.table'],
            $app['config']['session.lifetime'],
            $app,
        ));
    }

    /**
     * Die Trigger-Warnung steht auf jeder Seite und gehört deshalb in keinen
     * einzelnen Controller: Sie würde sonst an genau der Stelle fehlen, an der
     * jemand sie beim nächsten neuen Controller vergisst.
     *
     * Der try/catch ist kein Schmuck. Das Layout rendert auch die Fehlerseiten —
     * und die 500er ist gerade dann gefragt, wenn die Datenbank weg ist. Ohne
     * Auffangnetz erzeugte der Hinweis auf belastende Inhalte einen zweiten
     * Fehler mitten in der Fehlerseite.
     */
    private function triggerwarnungBereitstellen(): void
    {
        View::composer('layouts.app', function ($view) {
            $seite = null;
            $ersatz = null;

            try {
                $sprache = Language::aktuell();
                $seite = Triggerwarnung::fuer($sprache);

                // Nur kennzeichnen, wenn wirklich eine andere Sprache
                // ausgeliefert wird — sonst stünde lang="de" auf deutschem Text.
                if ($seite && $seite->locale !== $sprache->code) {
                    $ersatz = Language::finden($seite->locale);
                }
            } catch (Throwable $e) {
                // Ohne Datenbank keine Warnung. Die Seite bleibt benutzbar,
                // und der eigentliche Fehler wird nicht überdeckt.
            }

            $view->with([
                'triggerwarnung' => $seite,
                'triggerwarnungErsatz' => $ersatz,
            ]);
        });
    }
}
