<?php

namespace Tests\Feature;

use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\GroupRegistrations\Pages\ListGroupRegistrations;
use App\Filament\Verein\Resources\TeamsLinks\Pages\ListTeamsLinks;
use App\Models\Event;
use App\Models\Group;
use App\Models\Inquiry;
use App\Models\User;
use App\Notifications\NeueAnfrage;
use App\Notifications\NeueGruppenAnmeldung;
use Database\Seeders\AltseiteSeeder;
use Database\Seeders\VereinskontoSeeder;
use Filament\Auth\Pages\EditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Vereinsbereich unter /intern (Kevin und Nils, 30.10.2026): Der Verein
 * bearbeitet Veranstaltungen, Anmeldungen und Anfragen, kommt aber nicht in
 * die Verwaltung und kann damit keine Seiten der Website ändern.
 */
class VereinsbereichTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AltseiteSeeder::class);
    }

    public function test_ohne_anmeldung_geht_es_zum_login(): void
    {
        $this->get('/intern')->assertRedirect('/intern/login');
        $this->get('/intern/login')->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_vereinskonto_kommt_in_den_vereinsbereich_aber_nicht_in_die_verwaltung(): void
    {
        $verein = User::factory()->verein()->create();

        $this->actingAs($verein);
        foreach (['/intern', '/intern/events', '/intern/events/create', '/intern/group-registrations', '/intern/inquiries',
            '/intern/teams-links'] as $pfad) {
            $this->get($pfad)->assertOk("{$pfad} lädt nicht");
        }

        foreach (['/admin', '/admin/pages', '/admin/events', '/admin/inquiries'] as $pfad) {
            $this->get($pfad)->assertForbidden();
        }
    }

    public function test_normales_konto_kommt_nirgends_hinein(): void
    {
        $this->actingAs(User::factory()->create())->get('/intern')->assertForbidden();
    }

    public function test_verwaltung_darf_auch_in_den_vereinsbereich(): void
    {
        $this->actingAs(User::factory()->redaktion()->create())->get('/intern')->assertOk();
    }

    public function test_nur_die_drei_bereiche_sind_da(): void
    {
        $this->actingAs(User::factory()->verein()->create());

        $html = $this->get('/intern')->assertOk()
            ->assertSee('Veranstaltungen')->assertSee('Anmeldungen')->assertSee('Anfragen')
            ->getContent();

        foreach (['/intern/pages', '/intern/groups', '/intern/posts', '/intern/redirects', '/intern/team-members'] as $pfad) {
            $this->assertStringNotContainsString($pfad, $html);
            $this->get($pfad)->assertNotFound();
        }

        $this->assertSame(
            ['events', 'group-registrations', 'inquiries', 'teams-links'],
            collect(Filament::getPanel('verein')->getResources())
                ->map(fn ($resource) => $resource::getSlug())->sort()->values()->all(),
        );
    }

    public function test_startseite_verlinkt_in_den_vereinsbereich(): void
    {
        $this->actingAs(User::factory()->verein()->create());
        Group::where('slug', 'wir-sind-nicht-mehr-stumm')->firstOrFail()
            ->anmeldungen()->create(['email' => 'a@beispiel.de', 'zugestimmt_at' => now()]);

        $this->get('/intern')->assertOk()
            ->assertSee('/intern/group-registrations', false)
            ->assertDontSee('/admin/', false);
    }

    public function test_hinweismails_fuehren_in_den_vereinsbereich(): void
    {
        $anmeldung = Group::where('slug', 'wir-sind-nicht-mehr-stumm')->firstOrFail()
            ->anmeldungen()->create(['email' => 'a@beispiel.de', 'zugestimmt_at' => now()]);
        $anfrage = Inquiry::create([
            'name' => 'Kim', 'email' => 'kim@beispiel.de', 'betreff' => 'Frage', 'nachricht' => 'Hallo',
        ]);

        $empfaenger = new AnonymousNotifiable;
        $text = (string) (new NeueGruppenAnmeldung($anmeldung))->toMail($empfaenger)->render();
        $this->assertStringContainsString(url('/intern/group-registrations'), $text);

        $text = (string) (new NeueAnfrage($anfrage))->toMail($empfaenger)->render();
        $this->assertStringContainsString(url('/intern/inquiries/'.$anfrage->id.'/edit'), $text);
        $this->assertStringNotContainsString('/admin', $text);
    }

    public function test_ohne_teams_link_zeigt_der_hinweis_auf_teams_links(): void
    {
        // Im Vereinsbereich gibt es keine Gruppen-Seite, also auch nicht den
        // Weg „Gruppen → Teilnahme“ aus der Verwaltung.
        $gruppe = Group::where('slug', 'wir-sind-nicht-mehr-stumm')->firstOrFail();
        $gruppe->update(['zugangslink' => null]);
        $anmeldung = $gruppe->anmeldungen()->create(['email' => 'a@beispiel.de', 'zugestimmt_at' => now()]);

        Filament::setCurrentPanel(Filament::getPanel('verein'));

        Livewire::actingAs(User::factory()->verein()->create())
            ->test(ListGroupRegistrations::class)
            ->assertTableActionDisabled('freigeben', $anmeldung)
            ->assertSee('Bei der Gruppe ist noch kein Teams-Link eingetragen');
    }

    public function test_konto_befehl_legt_vereinskonto_an(): void
    {
        $this->artisan('verein:konto', ['email' => 'Taddi@Beispiel.de', 'name' => 'Taddi'])
            ->expectsOutputToContain('Konto angelegt: taddi@beispiel.de')
            ->expectsOutputToContain('Passwort (wird nur jetzt angezeigt):')
            ->assertSuccessful();

        $konto = User::where('email', 'taddi@beispiel.de')->firstOrFail();
        $this->assertTrue($konto->vereinsbereich);
        $this->assertFalse($konto->panel_zugang);

        // Zweiter Lauf setzt nur das Passwort neu, kein zweites Konto
        $altesPasswort = $konto->password;
        $this->artisan('verein:konto', ['email' => 'taddi@beispiel.de'])
            ->expectsOutputToContain('Passwort neu gesetzt')->assertSuccessful();
        $this->assertSame(1, User::where('email', 'taddi@beispiel.de')->count());
        $this->assertNotSame($altesPasswort, $konto->fresh()->password);
        $this->assertSame('Taddi', $konto->fresh()->name);

        $this->artisan('verein:konto', ['email' => 'keine-adresse'])->assertFailed();
    }

    public function test_lokales_vereinskonto(): void
    {
        $this->seed(VereinskontoSeeder::class);
        $this->seed(VereinskontoSeeder::class);

        $konto = User::where('email', VereinskontoSeeder::EMAIL)->sole();
        $this->assertTrue($konto->vereinsbereich);
        $this->assertFalse($konto->panel_zugang);
        $this->assertStringContainsString('VereinskontoSeeder', file_get_contents(base_path('bin/start')));
    }

    public function test_datumsfelder_in_deutscher_schreibweise(): void
    {
        // „18.11.2026, 18:30“ statt „Nov 18, 2026 18:30“ (Vorführvideo, 30.10.2026)
        $this->assertSame('d.m.Y, H:i', DateTimePicker::make('beginn')->seconds(false)->getDisplayFormat());
        $this->assertSame('d.m.Y, H:i:s', DateTimePicker::make('mit-sekunden')->getDisplayFormat());
        $this->assertSame('d.m.Y', DateTimePicker::make('tag')->time(false)->getDisplayFormat());
        $this->assertSame('j. F', DateTimePicker::make('eigen')->displayFormat('j. F')->getDisplayFormat());
    }

    public function test_teams_links_der_selbsthilfegruppen_pflegt_der_verein(): void
    {
        $gruppe = Group::where('slug', 'seelenfarben')->firstOrFail();
        $gruppe->update(['zugangslink' => null]);
        $nameVorher = $gruppe->getRawOriginal('name');
        $arbeitsgruppe = Group::where('typ', '!=', 'selbsthilfe')->firstOrFail();

        Filament::setCurrentPanel(Filament::getPanel('verein'));
        $this->actingAs(User::factory()->verein()->create());
        $seite = Livewire::test(ListTeamsLinks::class)
            ->assertCanSeeTableRecords([$gruppe])
            ->assertCanNotSeeTableRecords([$arbeitsgruppe]);

        // Nur Teams-Adressen
        $seite->callTableAction('edit', $gruppe, ['zugangslink' => 'https://beispiel.de/treffen', 'link_automatisch' => false])
            ->assertHasTableActionErrors(['zugangslink']);
        $this->assertNull($gruppe->fresh()->zugangslink);

        $seite = Livewire::test(ListTeamsLinks::class)->callTableAction('edit', $gruppe, [
            'zugangslink' => 'https://teams.microsoft.com/meet/123?p=abc', 'link_automatisch' => true,
        ])->assertHasNoTableActionErrors();

        $gruppe->refresh();
        $this->assertSame('https://teams.microsoft.com/meet/123?p=abc', $gruppe->zugangslink);
        $this->assertTrue($gruppe->link_automatisch);
        $this->assertSame($nameVorher, $gruppe->getRawOriginal('name'));
        // Verschlüsselt gespeichert, wie in der Verwaltung
        $this->assertStringNotContainsString('teams.microsoft.com', (string) DB::table('groups')->where('id', $gruppe->id)->value('zugangslink'));

        // Löschen oder neue Gruppen gibt es hier nicht
        $seite->assertTableActionDoesNotExist('delete');
        $this->get('/intern/teams-links/create')->assertNotFound();
    }

    public function test_veranstaltung_mit_oeffentlichem_teams_link(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('verein'));
        $formular = [
            'titel' => 'Infoabend online', 'slug' => 'infoabend-online', 'beginnt_am' => now()->addWeek()->setTime(18, 30),
            'online' => true, 'published_at' => now(),
        ];

        Livewire::actingAs(User::factory()->verein()->create())
            ->test(CreateEvent::class)
            ->fillForm($formular + ['teams_link' => 'https://zoom.us/j/123'])
            ->call('create')
            ->assertHasFormErrors(['teams_link'])
            ->fillForm($formular + ['teams_link' => 'https://teams.microsoft.com/meet/987?p=xyz'])
            ->call('create')
            ->assertHasNoFormErrors();

        $termin = Event::where('slug', 'infoabend-online')->sole();
        $this->get('/veranstaltungen/infoabend-online')->assertOk()
            ->assertSee('Über Microsoft Teams teilnehmen')
            ->assertSee('href="https://teams.microsoft.com/meet/987?p=xyz"', false);

        // Ohne Ende: bis vier Stunden nach Beginn, danach nicht mehr
        $this->travelTo($termin->beginnt_am->copy()->addHours(3));
        $this->assertTrue($termin->fresh()->teamsLinkZeigen());
        $this->travelTo($termin->beginnt_am->copy()->addHours(5));
        $this->assertFalse($termin->fresh()->teamsLinkZeigen());
        $this->travelBack();

        // Nicht mehr online: Link bleibt gespeichert, wird aber nicht gezeigt
        $termin->update(['online' => false]);
        $this->get('/veranstaltungen/infoabend-online')->assertDontSee('Über Microsoft Teams teilnehmen');
    }

    public function test_verein_aendert_sein_passwort_selbst(): void
    {
        $konto = User::factory()->verein()->create(['password' => 'startpasswort-123']);
        Filament::setCurrentPanel(Filament::getPanel('verein'));
        $this->actingAs($konto)->get('/intern/profile')->assertOk();

        // Ohne das alte Passwort geht es nicht
        Livewire::test(EditProfile::class)
            ->fillForm(['password' => 'Neues-Passwort-2026', 'passwordConfirmation' => 'Neues-Passwort-2026', 'currentPassword' => 'falsch'])
            ->call('save')
            ->assertHasFormErrors(['currentPassword']);
        $this->assertTrue(Hash::check('startpasswort-123', $konto->fresh()->password));

        Livewire::test(EditProfile::class)
            ->fillForm(['password' => 'Neues-Passwort-2026', 'passwordConfirmation' => 'Neues-Passwort-2026', 'currentPassword' => 'startpasswort-123'])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertTrue(Hash::check('Neues-Passwort-2026', $konto->fresh()->password));
        $this->assertTrue($konto->fresh()->vereinsbereich);
        $this->assertFalse($konto->fresh()->panel_zugang);
    }
}
