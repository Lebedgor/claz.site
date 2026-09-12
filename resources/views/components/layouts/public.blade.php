<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="alternate" type="application/rss+xml" title="{{ config('app.name') }}" href="{{ url('/rss.xml') }}">
    @stack('head')
</head>
<body class="min-h-screen bg-white font-sans text-zinc-900 antialiased">
<header class="sticky top-0 z-10 border-b border-zinc-200 bg-white/90 backdrop-blur">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
        <a href="{{ route('home') }}" class="text-lg font-semibold tracking-tight text-zinc-900">
            {{ config('app.name') }}<span class="text-indigo-600">.</span>
        </a>
        <nav class="flex items-center gap-1 text-sm">
            <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 font-medium text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900">{{ __('site.nav.home') }}</a>
            <a href="{{ route('tools.index') }}" class="rounded-lg px-3 py-2 font-medium text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900">{{ __('site.nav.tools') }}</a>
        </nav>
    </div>
    @if ($navCategories->isNotEmpty())
        <div class="mx-auto max-w-6xl overflow-x-auto px-4 pb-2">
            <div class="flex gap-2">
                @foreach ($navCategories as $navCategory)
                    <a href="{{ route('category.show', $navCategory) }}"
                       class="whitespace-nowrap rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-600 hover:bg-indigo-50 hover:text-indigo-700">
                        {{ $navCategory->getTranslation('name', app()->getLocale()) }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</header>
<div class="mx-auto max-w-6xl px-4">
    <x-banner placement="header" />
</div>
<main class="mx-auto max-w-6xl px-4 py-8">
    {{ $slot }}
</main>
<footer class="mt-8 border-t border-zinc-200 bg-zinc-50">
    <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-6 text-sm text-zinc-500 sm:flex-row sm:items-center sm:justify-between">
        <span>{{ config('app.name') }} — {{ __('site.tagline') }}</span>
        <span>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('site.footer.rights') }}</span>
    </div>
</footer>
</body>
</html>
