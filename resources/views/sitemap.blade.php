{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($staticUrls as $url)
    <url>
        <loc>{{ $url }}</loc>
    </url>
    @endforeach

    @foreach($eventUrls as $item)
    <url>
        <loc>{{ $item['loc'] }}</loc>
        @if(!empty($item['lastmod']))
        <lastmod>{{ $item['lastmod'] }}</lastmod>
        @endif
    </url>
    @endforeach

    @foreach($playerUrls as $item)
    <url>
        <loc>{{ $item['loc'] }}</loc>
        @if(!empty($item['lastmod']))
        <lastmod>{{ $item['lastmod'] }}</lastmod>
        @endif
    </url>
    @endforeach
</urlset>
