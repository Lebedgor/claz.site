@props([
    'title' => null,
    'description' => null,
    'type' => 'website',
    'image' => null,
    'jsonLd' => [],
])

@php
    $metaTitle = $title !== null
        ? $title.' — '.config('app.name')
        : config('app.name').' — '.__('site.tagline');
    $metaDescription = $description ?? __('site.meta_description');
    $url = request()->url();
    $locales = config('app.locales', [config('app.locale')]);
@endphp

<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<link rel="canonical" href="{{ $url }}">

@foreach ($locales as $locale)
    <link rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ $url }}">

<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $url }}">
@if ($image !== null)
    <meta property="og:image" content="{{ $image }}">
@endif
<meta name="twitter:card" content="{{ $image !== null ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">

@foreach ($jsonLd as $schema)
    <script type="application/ld+json">@json($schema)</script>
@endforeach
