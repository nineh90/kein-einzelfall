<?php

namespace Tests\Feature;

use App\Filament\Resources\GroupRegistrations\Pages\ListGroupRegistrations;
use App\Mail\AnmeldungEingegangen;
use App\Mail\GruppenZugang;
use App\Models\Group;
use App\Models\GroupRegistration;
use App\Models\Page;
use App\Models\User;
use App\Notifications\NeueGruppenAnmeldung;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\DatenschutzSeeder;
use Database\Seeders\UebersetzungenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * KEV-226: Anmeldung zu Selbsthilfegruppen über die Website, mit Zustimmung
 * zu Vereinbarung und Gruppenregeln; der Zugangslink kommt nach Freigabe im
 * Verwaltungsbereich oder, je Gruppe einstellbar, sofort.
 */
class GruppenAnmeldungTest extends TestCase
{
    use RefreshDatabase;

    private const SEITE = '/selbsthilfegruppen/wir-sind-nicht-mehr-stumm';

    private const LINK = 'https://teams.microsoft.com/l/meetup-join/geheim-123';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
        Group::where('slug', 'wir-sind-nicht-mehr-stumm')->update(['zugangslink' => encrypt(self::LINK, false)]);
    }

    private function gruppe(): Group
    {
        return Group::where('slug', 'wir-sind-nicht-mehr-stumm')->firstOrFail();
    }

    /** @return array<string, mixed> */
    private function daten(array $anders = []): array
    {
        return $anders + [
            'name' => 'Kim',
            'email' => 'kim@beispiel.de',
            'teilnahme' => '1',
            'gruppenregeln' => '1',
            'einwilligung' => '1',
            'herkunft' => ltrim(self::SEITE, '/'),
            'formular' => 'anmeldung',
            'webseite' => '',
            'gestartet_um' => encrypt(now()->subSeconds(30)->timestamp),
        ];
    }

    public function test_formular_nur_bei_offenen_selbsthilfegruppen(): void
    {
        $html = $this->get(self::SEITE)->assertOk()->getContent();

        $this->assertStringContainsString('id="anmeldung"', $html);
        $this->assertStringContainsString('href="#anmeldung"', $html);
        $this->assertStringContainsString('action="http://localhost'.self::SEITE.'/anmeldung"', $html);
        foreach (['teilnahme', 'gruppenregeln', 'einwilligung'] as $feld) {
            $this->assertMatchesRegularExpression('#name="'.$feld.'"[^>]*required#', $html);
        }
        // Die Häkchen verlinken die beiden PDFs aus der Liste auf /selbsthilfegruppen
        $this->assertMatchesRegularExpression('#<a href="/dokumente/[^"]+\.pdf" class="text-green-deep underline">Teilnahme- und Verschwiegenheitsvereinbarung</a>#', $html);
        $this->assertMatchesRegularExpression('#<a href="/dokumente/[^"]+\.pdf" class="text-green-deep underline">Gruppenregeln</a>#', $html);
        // Der Zugangslink steht nirgends auf der Seite
        $this->assertStringNotContainsString('teams.microsoft.com', $html);
        $this->assertStringNotContainsString('Per E-Mail anmelden', $html);

        // In Planung: kein Formular, die Mail bleibt
        $this->get('/selbsthilfegruppen/seelenfarben')->assertOk()
            ->assertDontSee('id="anmeldung"', false)->assertSee('Per E-Mail nachfragen');
        // Arbeitsgruppen melden sich weiter per Mail an
        $this->get('/arbeitsgruppen/ag-01-ser')->assertOk()->assertDontSee('id="anmeldung"', false);
    }

    public function test_anmeldung_verschluesselt_gespeichert_bestaetigt_und_gemeldet(): void
    {
        Mail::fake();
        Notification::fake();

        $this->post(self::SEITE.'/anmeldung', $this->daten())
            ->assertRedirect('http://localhost'.self::SEITE.'#formular-anmeldung')
            ->assertSessionHas('anmeldung_ergebnis', 'bestaetigt');

        $anmeldung = GroupRegistration::sole();
        $this->assertSame('kim@beispiel.de', $anmeldung->email);
        $this->assertSame('Kim', $anmeldung->name);
        $this->assertSame('offen', $anmeldung->status);
        $this->assertNotNull($anmeldung->zugestimmt_at);
        // In der Datenbank kein Klartext
        $roh = DB::table('group_registrations')->first();
        $this->assertStringNotContainsString('kim@beispiel.de', $roh->email);
        $this->assertStringNotContainsString('Kim', (string) $roh->name);

        Mail::assertSent(AnmeldungEingegangen::class, fn ($mail) => $mail->hasTo('kim@beispiel.de') && $mail->locale === 'de');
        Mail::assertNotSent(GruppenZugang::class);

        // Der Verein erfährt nur, dass etwas da ist: ohne Name und Adresse
        Notification::assertSentOnDemand(NeueGruppenAnmeldung::class, function ($hinweis, $kanaele, $empfaenger) {
            $text = (string) $hinweis->toMail($empfaenger)->render();

            return $empfaenger->routes['mail'] === 'selbsthilfe@kein-einzelfall.de'
                && str_contains($text, 'Wir sind nicht mehr stumm')
                && str_contains($text, '„Freigeben und Link senden“')
                && ! str_contains($text, 'Alle Rechte vorbehalten')
                && ! str_contains($text, 'kim@beispiel.de') && ! str_contains($text, 'Kim');
        });

        // Die Bestätigung: Termin, Dokumente, kein Link
        $text = (new AnmeldungEingegangen($this->gruppe(), 'Kim'))->render();
        $this->assertStringContainsString('Hallo Kim,', $text);
        $this->assertStringContainsString('Nächster Termin: Mittwoch', $text);
        $this->assertStringNotContainsString('teams.microsoft.com', $text);
    }

    public function test_pflichtfelder_honigtopf_und_zeitfalle(): void
    {
        Mail::fake();

        $this->post(self::SEITE.'/anmeldung', $this->daten(['email' => '', 'teilnahme' => null, 'gruppenregeln' => null, 'einwilligung' => null]))
            ->assertSessionHasErrors(['email', 'teilnahme', 'gruppenregeln', 'einwilligung']);
        $this->post(self::SEITE.'/anmeldung', $this->daten(['webseite' => 'http://spam.example']))
            ->assertSessionHasErrors('webseite');
        $this->post(self::SEITE.'/anmeldung', $this->daten(['gestartet_um' => encrypt(now()->timestamp)]))
            ->assertSessionHasErrors();

        $this->assertSame(0, GroupRegistration::count());
        Mail::assertNothingSent();
    }

    public function test_gruppe_in_planung_nimmt_keine_anmeldung(): void
    {
        $this->post('/selbsthilfegruppen/seelenfarben/anmeldung', $this->daten(['herkunft' => 'selbsthilfegruppen/seelenfarben']))
            ->assertRedirect();
        $this->post('/selbsthilfegruppen/gibt-es-nicht/anmeldung', $this->daten())->assertNotFound();

        $this->assertSame(0, GroupRegistration::count());
    }

    public function test_sofort_mit_link_wenn_die_gruppe_es_will(): void
    {
        Mail::fake();
        Notification::fake();
        Group::where('slug', 'wir-sind-nicht-mehr-stumm')->update(['link_automatisch' => true]);

        $this->post(self::SEITE.'/anmeldung', $this->daten())->assertSessionHas('anmeldung_ergebnis', 'link');

        $anmeldung = GroupRegistration::sole();
        $this->assertSame('freigegeben', $anmeldung->status);
        $this->assertNotNull($anmeldung->link_versendet_at);
        $this->assertNotNull($anmeldung->bearbeitet_at);
        Mail::assertSent(GruppenZugang::class, fn ($mail) => $mail->hasTo('kim@beispiel.de'));
        Mail::assertNotSent(AnmeldungEingegangen::class);

        $text = (new GruppenZugang($this->gruppe(), 'Kim'))->render();
        $this->assertStringContainsString(self::LINK, $text);
        $this->assertStringContainsString('Bitte gib den Link nicht weiter', $text);

        // Ohne Link bei der Gruppe: doch nur die Bestätigung
        Group::where('slug', 'wir-sind-nicht-mehr-stumm')->update(['zugangslink' => null]);
        $this->post(self::SEITE.'/anmeldung', $this->daten(['email' => 'zwei@beispiel.de']))
            ->assertSessionHas('anmeldung_ergebnis', 'bestaetigt');
    }

    public function test_freigeben_und_ablehnen_im_verwaltungsbereich(): void
    {
        Mail::fake();
        $gruppe = $this->gruppe();
        $erste = $gruppe->anmeldungen()->create(['email' => 'a@beispiel.de', 'zugestimmt_at' => now()]);
        $zweite = $gruppe->anmeldungen()->create(['email' => 'b@beispiel.de', 'zugestimmt_at' => now()]);

        Livewire::actingAs(User::factory()->redaktion()->create())
            ->test(ListGroupRegistrations::class)
            ->assertCanSeeTableRecords([$erste, $zweite])
            ->callTableAction('freigeben', $erste)
            ->callTableAction('ablehnen', $zweite);

        $this->assertSame('freigegeben', $erste->fresh()->status);
        $this->assertSame('abgelehnt', $zweite->fresh()->status);
        Mail::assertSent(GruppenZugang::class, 1);
        Mail::assertSent(GruppenZugang::class, fn ($mail) => $mail->hasTo('a@beispiel.de'));

        // Ohne Link bei der Gruppe ist Freigeben nicht möglich
        Group::whereKey($gruppe->id)->update(['zugangslink' => null]);
        $dritte = $gruppe->anmeldungen()->create(['email' => 'c@beispiel.de', 'zugestimmt_at' => now()]);
        Livewire::actingAs(User::factory()->redaktion()->create())
            ->test(ListGroupRegistrations::class)
            ->assertTableActionDisabled('freigeben', $dritte);
        $this->assertSame('offen', $dritte->fresh()->status);
    }

    /** Durchlauf 30.10.2026: Die Startseite des Panels zeigt, was wartet, statt Filament-Kästen. */
    public function test_startseite_des_verwaltungsbereichs_zeigt_was_wartet(): void
    {
        $this->actingAs(User::factory()->redaktion()->create());

        $leer = $this->get('/admin')->assertOk()->getContent();
        $this->assertStringContainsString('Keine neuen Anmeldungen.', $leer);
        $this->assertStringNotContainsString('filamentphp.com', $leer);
        $this->assertStringNotContainsString('Dashboard', $leer);

        $this->gruppe()->anmeldungen()->create(['email' => 'a@beispiel.de', 'zugestimmt_at' => now()]);
        $this->get('/admin')->assertOk()
            ->assertSee('1 neue Anmeldung wartet auf deine Freigabe.')
            ->assertSee('Anmeldungen ansehen')
            ->assertSee('/admin/group-registrations', false);
    }

    public function test_aufbewahrungsfrist(): void
    {
        $gruppe = $this->gruppe();
        $neu = fn (array $d) => $gruppe->anmeldungen()->create(['email' => 'x@beispiel.de', 'zugestimmt_at' => now()] + $d);

        $alt = $neu(['status' => 'freigegeben']);
        $alt->forceFill(['bearbeitet_at' => now()->subDays(31)])->saveQuietly();
        $frisch = $neu(['status' => 'abgelehnt']);
        $liegtLange = $neu([]);
        $liegtLange->forceFill(['created_at' => now()->subDays(91)])->saveQuietly();
        $liegtKurz = $neu([]);

        $this->artisan('anmeldungen:aufraeumen')->assertSuccessful();

        $this->assertModelMissing($alt);
        $this->assertModelMissing($liegtLange);
        $this->assertModelExists($frisch);
        $this->assertModelExists($liegtKurz);
    }

    public function test_auf_dem_server_ohne_echten_mailversand_nichts_ins_log(): void
    {
        Mail::fake();
        Notification::fake();
        $this->app['env'] = 'production';
        config(['mail.default' => 'log']);

        // Mit MAIL_MAILER=log stünden Adresse und Link im Klartext im Log:
        // dann gar nicht senden, die Anmeldung bleibt trotzdem.
        $this->assertFalse(\App\Support\GruppenAnmeldung::versandMoeglich());
        $this->assertSame('gespeichert', \App\Support\GruppenAnmeldung::annehmen($this->gruppe(), 'kim@beispiel.de', null));

        $this->assertSame(1, GroupRegistration::count());
        Mail::assertNothingSent();
        Notification::assertNothingSent();
    }

    public function test_formularbremse(): void
    {
        Mail::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->post(self::SEITE.'/anmeldung', $this->daten(['email' => "p{$i}@beispiel.de"]));
        }
        $this->post(self::SEITE.'/anmeldung', $this->daten(['email' => 'p6@beispiel.de']))
            ->assertSessionHas('formular_hinweis');

        $this->assertSame(5, GroupRegistration::count());
    }

    public function test_englisch_und_datenschutz(): void
    {
        Mail::fake();
        $this->seed(UebersetzungenSeeder::class);

        $this->get('/en'.self::SEITE)->assertOk()
            ->assertSee('Sign up now')
            ->assertSee('I have read and agree to the', false)
            ->assertSee('action="http://localhost/en'.self::SEITE.'/anmeldung"', false);

        $this->post('/en'.self::SEITE.'/anmeldung', $this->daten(['herkunft' => 'en'.self::SEITE]));
        Mail::assertSent(AnmeldungEingegangen::class, fn ($mail) => $mail->locale === 'en');
        $this->assertSame('en', GroupRegistration::sole()->locale);

        $this->get('/datenschutz')->assertSee('Anmeldung zu Selbsthilfegruppen')
            ->assertSee('Art. 9 Abs. 2 lit. a');
        $this->get('/en/datenschutz')->assertSee('Signing up for self-help groups');

        // Kommt die Migration noch einmal, bleibt es ein Abschnitt
        DatenschutzSeeder::anmeldungEinfuegen(Page::where('slug', 'datenschutz')->where('locale', 'de')->firstOrFail());
        $this->assertSame(1, Page::where('slug', 'datenschutz')->where('locale', 'de')->firstOrFail()->blocks()->get()
            ->filter(fn ($b) => ($b->data['titel'] ?? null) === DatenschutzSeeder::ANMELDUNG['titel'])->count());
    }
}
