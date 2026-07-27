<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($eintraege as $eintrag)
    <url>
        <loc>{{ $eintrag['url'] }}</loc>
@if ($eintrag['geaendert'])
        <lastmod>{{ $eintrag['geaendert'] }}</lastmod>
@endif
{{-- Sprachfassungen verweisen gegenseitig aufeinander. Der Verweis auf sich
     selbst gehört ausdrücklich dazu — so verlangt es die Spezifikation. --}}
@foreach ($eintrag['alternativen'] as $code => $adresse)
        <xhtml:link rel="alternate" hreflang="{{ $code }}" href="{{ $adresse }}"/>
@endforeach
    </url>
@endforeach
</urlset>
