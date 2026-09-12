{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title>{{ config('app.name') }} — {{ __('site.tagline') }}</title>
    <link>{{ url('/') }}</link>
    <description>{{ __('site.meta_description') }}</description>
    <language>{{ app()->getLocale() }}</language>
    <atom:link href="{{ url('/rss.xml') }}" rel="self" type="application/rss+xml" />
@foreach ($articles as $article)
    <item>
        <title>{{ $article['title'] }}</title>
        <link>{{ $article['url'] }}</link>
        <guid>{{ $article['url'] }}</guid>
        <description><![CDATA[{{ str_replace(']]>', ']]&gt;', $article['description']) }}]]></description>
        <pubDate>{{ $article['date'] }}</pubDate>
@if ($article['category'] !== null)
        <category>{{ $article['category'] }}</category>
@endif
    </item>
@endforeach
</channel>
</rss>
