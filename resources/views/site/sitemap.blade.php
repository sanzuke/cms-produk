<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($urls as [$loc, $lastmod, $freq, $priority])
  <url>
    <loc>{{ $loc }}</loc>
@if ($lastmod)
    <lastmod>{{ $lastmod->toAtomString() }}</lastmod>
@endif
    <changefreq>{{ $freq }}</changefreq>
    <priority>{{ $priority }}</priority>
  </url>
@endforeach
</urlset>
