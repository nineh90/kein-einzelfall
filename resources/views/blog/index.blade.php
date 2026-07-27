@extends('layouts.app')

@section('title', $aktiveKategorie?->name ?? 'Aktuelles')
@section('description', 'Neuigkeiten und Beiträge des KE!N EINZELFALL e.V.')

@section('content')
<div class="px-4 py-8 lg:px-10 lg:py-12">
    <div class="mx-auto max-w-6xl">

        <h1 class="font-display text-[1.75rem] font-medium text-ink lg:text-4xl">
            {{ $aktiveKategorie?->name ?? 'Aktuelles' }}
        </h1>
        @if ($aktiveKategorie?->beschreibung)
            <p class="mt-2 max-w-prose text-ink-soft">{{ $aktiveKategorie->beschreibung }}</p>
        @endif

        {{-- Suche und Filter als ganz normales GET-Formular: Jeder Stand hat
             eine eigene Adresse, die man teilen und speichern kann, und die
             ohne JavaScript funktioniert. --}}
        <form method="GET" action="{{ route('blog.index') }}" role="search"
              class="mt-6 flex flex-wrap items-end gap-3">
            <div class="min-w-56 flex-1">
                <label for="suche" class="mb-1.5 block text-sm font-medium text-ink">
                    Beiträge durchsuchen
                </label>
                <input type="search" name="suche" id="suche" value="{{ $suchbegriff }}"
                       placeholder="Stichwort eingeben"
                       class="w-full rounded-lg border border-line bg-card px-4 py-2.5 text-ink">
            </div>

            @if ($aktiveKategorie)
                <input type="hidden" name="kategorie" value="{{ $aktiveKategorie->slug }}">
            @endif

            <button type="submit"
                    class="rounded-full bg-green px-5 py-2.5 font-medium text-on-green hover:bg-green-deep">
                Suchen
            </button>

            @if ($suchbegriff || $aktiveKategorie)
                <a href="{{ route('blog.index') }}"
                   class="rounded-full border border-line px-5 py-2.5 text-ink-soft no-underline hover:bg-card">
                    Zurücksetzen
                </a>
            @endif
        </form>

        @if ($kategorien->isNotEmpty())
            <nav aria-label="Kategorien" class="mt-4">
                <ul class="flex flex-wrap gap-2">
                    <li>
                        <a href="{{ route('blog.index', $suchbegriff ? ['suche' => $suchbegriff] : []) }}"
                           @if (! $aktiveKategorie) aria-current="page" @endif
                           class="inline-block rounded-full border border-line px-4 py-1.5 text-sm no-underline
                                  text-ink-soft hover:bg-card
                                  aria-[current=page]:border-green aria-[current=page]:bg-green
                                  aria-[current=page]:text-on-green">
                            Alle
                        </a>
                    </li>
                    @foreach ($kategorien as $kategorie)
                        <li>
                            <a href="{{ route('blog.index', array_filter([
                                    'kategorie' => $kategorie->slug,
                                    'suche' => $suchbegriff ?: null,
                               ])) }}"
                               @if ($aktiveKategorie?->is($kategorie)) aria-current="page" @endif
                               class="inline-block rounded-full border border-line px-4 py-1.5 text-sm no-underline
                                      text-ink-soft hover:bg-card
                                      aria-[current=page]:border-green aria-[current=page]:bg-green
                                      aria-[current=page]:text-on-green">
                                {{ $kategorie->name }}
                                <span class="text-xs opacity-70">({{ $kategorie->posts_count }})</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endif

        {{-- Trefferzahl als role="status": Screenreader melden nach dem Absenden,
             was die Suche ergeben hat, ohne dass man erst weitertabbt. --}}
        <p role="status" class="mt-6 text-sm text-ink-soft">
            @if ($suchbegriff)
                {{ trans_choice('{0}Keine Beiträge|{1}Ein Beitrag|[2,*]:count Beiträge',
                   $beitraege->total(), ['count' => $beitraege->total()]) }}
                für „{{ $suchbegriff }}“
            @else
                {{ trans_choice('{0}Noch keine Beiträge|{1}Ein Beitrag|[2,*]:count Beiträge',
                   $beitraege->total(), ['count' => $beitraege->total()]) }}
            @endif
        </p>

        @if ($beitraege->isEmpty())
            <div class="mt-6 rounded-card border border-line bg-card px-6 py-10 text-center">
                <p class="text-ink">Hier ist noch nichts zu finden.</p>
                @if ($suchbegriff || $aktiveKategorie)
                    <p class="mt-2 text-sm text-ink-soft">
                        Versuch es mit einem anderen Stichwort oder
                        <a href="{{ route('blog.index') }}" class="text-green-deep underline">zeig alle Beiträge</a>.
                    </p>
                @endif
            </div>
        @else
            <ul class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($beitraege as $beitrag)
                    <li class="flex">
                        <article class="flex flex-1 flex-col overflow-hidden rounded-card border border-line bg-card">
                            @if ($beitrag->bild_pfad)
                                <img src="{{ $beitrag->bild_pfad }}" alt="{{ $beitrag->bild_alt }}"
                                     loading="lazy" class="h-40 w-full object-cover">
                            @endif

                            <div class="flex flex-1 flex-col p-5">
                                <p class="text-xs uppercase tracking-[0.04em] text-ink-soft">
                                    <time datetime="{{ $beitrag->published_at->toDateString() }}">
                                        {{ $beitrag->published_at->format('d.m.Y') }}
                                    </time>
                                    @if ($beitrag->category)
                                        · {{ $beitrag->category->name }}
                                    @endif
                                </p>

                                <h2 class="mt-1.5 font-display text-lg font-semibold text-ink">
                                    {{-- Der Link umfasst nur die Überschrift: „Weiterlesen“ dreimal
                                         auf einer Seite wäre für Screenreader nicht unterscheidbar. --}}
                                    <a href="{{ route('blog.show', $beitrag->slug) }}"
                                       class="text-ink no-underline hover:underline">
                                        {{ $beitrag->titel }}
                                    </a>
                                </h2>

                                <p class="mt-2 flex-1 text-sm leading-relaxed text-ink-soft">
                                    {{ $beitrag->anriss() }}
                                </p>
                            </div>
                        </article>
                    </li>
                @endforeach
            </ul>

            <div class="mt-8">
                {{ $beitraege->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
