@props([
    'title' => null,
    'description' => null,
    'type' => 'website',
    'image' => null,
    'jsonLd' => [],
    'robots' => null,
    'publishedTime' => null,
    'modifiedTime' => null,
    'section' => null,
    'tags' => [],
])

@php
    $metaTitle = $title !== null
        ? $title.' — '.config('app.name')
        : config('app.name').' — '.__('site.tagline');
    $metaDescription = $description ?? __('site.meta_description');
    $locale = app()->getLocale();
    $defaultLocale = config('app.locale');
    $locales = config('app.locales', [$defaultLocale]);
    $path = strval(request()->path()) === '/' ? '' : strval(request()->path());

    $localeUrl = function (string $code) use ($path, $defaultLocale): string {
        $prefix = $code === $defaultLocale ? '' : '/'.$code;

        return url($prefix === '' ? '/'.$path : $prefix.'/'.$path);
    };

    $canonical = $localeUrl($locale);
    $page = request()->query('page');

    if ($page !== null && is_numeric($page) && (int) $page > 1) {
        $canonical .= '?page='.(int) $page;
    }

    $ogLocale = str_contains($locale, '-') ? str_replace('-', '_', $locale) : $locale.'_'.$locale;
@endphp

<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
@if (filled($robots))
    <meta name="robots" content="{{ $robots }}">
@endif
<link rel="canonical" href="{{ $canonical }}">

@foreach ($locales as $code)
    <link rel="alternate" hreflang="{{ $code }}" href="{{ $localeUrl($code) }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ $localeUrl($defaultLocale) }}">

<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:locale" content="{{ $ogLocale }}">
@if ($image !== null)
    <meta property="og:image" content="{{ url($image) }}">
    <meta property="og:image:alt" content="{{ $metaTitle }}">
@endif
@if ($type === 'article')
    @if ($publishedTime !== null)
        <meta property="article:published_time" content="{{ $publishedTime }}">
    @endif
    @if ($modifiedTime !== null)
        <meta property="article:modified_time" content="{{ $modifiedTime }}">
    @endif
    @if ($section !== null)
        <meta property="article:section" content="{{ $section }}">
    @endif
    @foreach ($tags as $tag)
        <meta property="article:tag" content="{{ $tag }}">
    @endforeach
@endif
<meta name="twitter:card" content="{{ $image !== null ? 'summary_large_image' : 'summary' }}">
@if ($image !== null)
    <meta name="twitter:image" content="{{ url($image) }}">
@endif
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">

@foreach ($jsonLd as $schema)
    <script type="application/ld+json">@json($schema)</script>
@endforeach