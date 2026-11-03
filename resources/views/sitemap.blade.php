{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ route('home') }}</loc><changefreq>hourly</changefreq></url>
    <url><loc>{{ route('about') }}</loc></url>
    <url><loc>{{ route('support') }}</loc></url>
@foreach ($events as $event)
    <url><loc>{{ route('events.show', [$event, $event->slug()]) }}</loc><lastmod>{{ $event->updated_at->toAtomString() }}</lastmod></url>
@endforeach
</urlset>
