<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($locales as $locale)
        <sitemap>
            <loc>{{ url("/{$locale}/sitemap.xml") }}</loc>
            <lastmod>{{ now()->toAtomString() }}</lastmod>
        </sitemap>
    @endforeach
</sitemapindex>
