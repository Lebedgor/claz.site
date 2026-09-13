{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">
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
@if ($article['cover'] !== null)
        <enclosure url="{{ url($article['cover']) }}" type="image/{{ pathinfo($article['cover'], PATHINFO_EXTENSION) === 'png' ? 'png' : 'jpeg' }}" length="0"/>
@endif
        <content:encoded><![CDATA[{{ str_replace(']]>', ']]&gt;', $article['content']) }}]]></content:encoded>
    </item>
@endforeach
</channel>
</rss>