<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

/**
 * Blog / Aktuelles.
 *
 * Suche und Filter laufen bewusst über GET-Parameter statt über Livewire:
 * Jeder Filterstand hat damit eine eigene Adresse, die man teilen, als
 * Lesezeichen speichern und die eine Suchmaschine erfassen kann. Genau
 * diesen Fehler macht die Altseite mit ihrer per JavaScript erzeugten
 * Navigation.
 */
class BlogController extends Controller
{
    public function index(Request $request)
    {
        $suchbegriff = $request->string('suche')->trim()->value();
        $kategorieSlug = $request->string('kategorie')->trim()->value();

        $kategorie = $kategorieSlug
            ? Category::where('slug', $kategorieSlug)->first()
            : null;

        $beitraege = Post::veroeffentlicht()
            ->with('category')
            ->when($kategorie, fn ($q) => $q->where('category_id', $kategorie->id))
            ->suche($suchbegriff)
            ->latest('published_at')
            ->paginate(9)
            // Ohne das verliert die Blätter-Navigation Suche und Filter.
            ->withQueryString();

        return view('blog.index', [
            'beitraege' => $beitraege,
            'kategorien' => Category::withCount(['posts' => fn ($q) => $q->veroeffentlicht()])
                ->having('posts_count', '>', 0)
                ->orderBy('name')
                ->get(),
            'aktiveKategorie' => $kategorie,
            'suchbegriff' => $suchbegriff,
        ]);
    }

    public function show(string $slug)
    {
        $beitrag = Post::veroeffentlicht()->with('category')->where('slug', $slug)->firstOrFail();

        $weitere = Post::veroeffentlicht()
            ->where('id', '!=', $beitrag->id)
            ->when($beitrag->category_id, fn ($q) => $q->where('category_id', $beitrag->category_id))
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('blog.show', compact('beitrag', 'weitere'));
    }
}
