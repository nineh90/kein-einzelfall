<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Redirect;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::veroeffentlicht()
            ->with('blocks')
            ->where('slug', $slug)
            ->first();

        if ($page) {
            return view('page', compact('page'));
        }

        // Kein Treffer heißt nicht automatisch 404: Adressen wie /impressum-2
        // sehen aus wie ein gültiger Slug, sind aber Altlasten der WordPress-Seite
        // und müssen dauerhaft weitergeleitet werden.
        return $this->weiterleitenOder404($slug);
    }

    private function weiterleitenOder404(string $pfad)
    {
        $regel = Redirect::whereIn('von', [$pfad, $pfad.'/'])->first();

        if (! $regel) {
            throw new NotFoundHttpException;
        }

        $regel->benutzt();

        return redirect($regel->nach, $regel->status);
    }
}
