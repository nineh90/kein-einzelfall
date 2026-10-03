<?php

use App\Models\Page;
use App\Support\Titelbilder;
use Illuminate\Database\Migrations\Migration;

/**
 * Seite „Landesstellen“ als Entwurf, mit Titelbild von Taddi (KEV-78).
 *
 * Wie die anderen neuen Bereiche (NeueBereicheSeeder): angelegt, aber nicht
 * veröffentlicht und nicht im Menü, bis der Text kommt. Gibt es die Seite
 * schon, bleibt sie, wie sie ist.
 */
return new class extends Migration
{
    public function up(): void
    {
        $seite = Page::firstOrCreate(
            ['slug' => 'landesstellen', 'locale' => 'de'],
            [
                'titel' => 'Landesstellen',
                'meta_title' => 'Landesstellen - Kein Einzelfall e.V.',
                'published_at' => null,
            ],
        );

        if ($seite->wasRecentlyCreated) {
            $seite->blocks()->create(['typ' => 'text', 'position' => 0, 'data' => []]);
        }

        Titelbilder::setzen();
    }

    public function down(): void
    {
        Page::where('slug', 'landesstellen')->whereNull('published_at')->each(function (Page $seite) {
            $seite->blocks()->delete();
            $seite->delete();
        });
    }
};
