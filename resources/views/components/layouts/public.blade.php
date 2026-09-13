<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/ex-article.css') }}">
    <link rel="alternate" type="application/rss+xml" title="{{ config('app.name') }}" href="{{ url('/rss.xml') }}">
    @stack('head')
</head>
@php
    $isActiveHome = request()->routeIs('home');
    $isActiveTools = request()->routeIs('tools.index');
@endphp
<body class="min-h-screen bg-cream font-sans text-zinc-900 antialiased" x-data="{ menuOpen: false }">
<header class="sticky top-0 z-30 border-b border-zinc-200/70 bg-white/80 backdrop-blur-xl">
    <div aria-hidden="true" class="h-0.5 bg-linear-to-r from-indigo-600 via-violet-600 to-fuchsia-500"></div>
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <span aria-hidden="true" class="flex h-9 w-9 items-center justify-center rounded-xl bg-linear-to-br from-indigo-600 via-violet-600 to-fuchsia-500 text-base font-extrabold text-white shadow-lg shadow-indigo-500/30">
                {{ mb_strtoupper(mb_substr(config('app.name'), 0, 1)) }}
            </span>
            <span class="text-lg font-bold tracking-tight text-zinc-900">
                {{ config('app.name') }}<span class="text-gradient">.</span>
            </span>
        </a>

        <nav aria-label="Main" class="hidden items-center gap-1 text-sm md:flex">
            <a href="{{ route('home') }}"
               class="relative rounded-lg px-3 py-2 font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 {{ $isActiveHome ? 'font-semibold text-zinc-900 after:absolute after:inset-x-3 after:bottom-0.5 after:h-0.5 after:rounded-full after:bg-linear-to-r after:from-indigo-500 after:to-fuchsia-500' : '' }}">
                {{ __('site.nav.home') }}
            </a>
            <a href="{{ route('tools.index') }}"
               class="relative rounded-lg px-3 py-2 font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 {{ $isActiveTools ? 'font-semibold text-zinc-900 after:absolute after:inset-x-3 after:bottom-0.5 after:h-0.5 after:rounded-full after:bg-linear-to-r after:from-indigo-500 after:to-fuchsia-500' : '' }}">
                {{ __('site.nav.tools') }}
            </a>
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ route('tools.index') }}"
               class="hidden items-center gap-1.5 rounded-xl bg-linear-to-r from-indigo-600 to-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:shadow-indigo-500/40 hover:brightness-110 sm:inline-flex">
                <x-heroicon-o-squares-2x2 class="h-4 w-4" />
                {{ __('site.nav.browse') }}
            </a>
            <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 md:hidden"
                    @click="menuOpen = !menuOpen" :aria-expanded="menuOpen" aria-label="{{ __('site.nav.menu') }}" aria-controls="mobile-menu">
                <span x-show="!menuOpen" x-cloak>
                    <x-heroicon-o-bars-3 class="h-6 w-6" />
                </span>
                <span x-show="menuOpen" x-cloak>
                    <x-heroicon-o-x-mark class="h-6 w-6" />
                </span>
            </button>
        </div>
    </div>

    @if ($navCategories->isNotEmpty())
        <div class="mx-auto hidden max-w-6xl overflow-x-auto scrollbar-none px-4 py-2.5 md:block">
            <div class="flex gap-2">
                @foreach ($navCategories as $navCategory)
                    <a href="{{ route('category.show', $navCategory) }}"
                       class="whitespace-nowrap rounded-full bg-white px-3 py-1.5 text-xs font-medium text-zinc-600 ring-1 ring-zinc-200 transition hover:bg-indigo-50 hover:text-indigo-700 hover:ring-indigo-300">
                        {{ $navCategory->getTranslation('name', app()->getLocale()) }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div id="mobile-menu" x-show="menuOpen" x-cloak x-transition.opacity.duration.150ms
         class="border-t border-zinc-200/70 bg-white/95 px-4 py-4 backdrop-blur md:hidden">
        <nav aria-label="Mobile" class="flex flex-col gap-1 text-sm">
            <a href="{{ route('home') }}" @click="menuOpen = false"
               class="rounded-xl px-3 py-2.5 font-medium text-zinc-700 transition hover:bg-zinc-100 {{ $isActiveHome ? 'bg-indigo-50 font-semibold text-indigo-700' : '' }}">
                {{ __('site.nav.home') }}
            </a>
            <a href="{{ route('tools.index') }}" @click="menuOpen = false"
               class="rounded-xl px-3 py-2.5 font-medium text-zinc-700 transition hover:bg-zinc-100 {{ $isActiveTools ? 'bg-indigo-50 font-semibold text-indigo-700' : '' }}">
                {{ __('site.nav.tools') }}
            </a>
            <a href="{{ route('tools.index') }}" @click="menuOpen = false"
               class="mt-2 inline-flex items-center justify-center gap-1.5 rounded-xl bg-linear-to-r from-indigo-600 to-violet-600 px-4 py-2.5 font-semibold text-white shadow-lg shadow-indigo-500/25">
                <x-heroicon-o-squares-2x2 class="h-4 w-4" />
                {{ __('site.nav.browse') }}
            </a>
        </nav>
        @if ($navCategories->isNotEmpty())
            <div class="mt-3 border-t border-zinc-100 pt-3">
                <div class="flex flex-wrap gap-2">
                    @foreach ($navCategories as $navCategory)
                        <a href="{{ route('category.show', $navCategory) }}" @click="menuOpen = false"
                           class="rounded-full bg-zinc-100 px-3 py-1.5 text-xs font-medium text-zinc-600 transition hover:bg-indigo-50 hover:text-indigo-700">
                            {{ $navCategory->getTranslation('name', app()->getLocale()) }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</header>

<div class="mx-auto max-w-6xl px-4">
    <x-banner placement="header" />
</div>

<main class="mx-auto max-w-6xl px-4 py-8 sm:py-10">
    {{ $slot }}
</main>

<footer class="mt-16 bg-zinc-950 text-zinc-400">
    <div aria-hidden="true" class="h-px bg-linear-to-r from-indigo-500 via-fuchsia-500 to-transparent"></div>
    <div class="mx-auto max-w-6xl px-4 py-12">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-[1.5fr_1fr_1fr]">
            <div>
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <span aria-hidden="true" class="flex h-9 w-9 items-center justify-center rounded-xl bg-linear-to-br from-indigo-600 via-violet-600 to-fuchsia-500 text-base font-extrabold text-white">
                        {{ mb_strtoupper(mb_substr(config('app.name'), 0, 1)) }}
                    </span>
                    <span class="text-lg font-bold tracking-tight text-white">
                        {{ config('app.name') }}<span class="text-gradient">.</span>
                    </span>
                </a>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-zinc-500">{{ __('site.tagline') }}.</p>
            </div>
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-widest text-zinc-500">{{ __('site.footer.explore') }}</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('home') }}" class="transition hover:text-white">{{ __('site.nav.home') }}</a></li>
                    <li><a href="{{ route('tools.index') }}" class="transition hover:text-white">{{ __('site.nav.tools') }}</a></li>
                    <li><a href="{{ url('/sitemap.xml') }}" class="transition hover:text-white">{{ __('site.footer.sitemap') }}</a></li>
                    <li><a href="{{ url('/rss.xml') }}" class="transition hover:text-white">{{ __('site.footer.rss') }}</a></li>
                </ul>
            </div>
            @if ($navCategories->isNotEmpty())
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-widest text-zinc-500">{{ __('site.footer.categories') }}</h3>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        @foreach ($navCategories->take(6) as $navCategory)
                            <li>
                                <a href="{{ route('category.show', $navCategory) }}" class="transition hover:text-white">
                                    {{ $navCategory->getTranslation('name', app()->getLocale()) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        <div class="mt-10 flex flex-col gap-2 border-t border-white/10 pt-6 text-xs text-zinc-500 sm:flex-row sm:items-center sm:justify-between">
            <span>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('site.footer.rights') }}</span>
            <span>{{ __('site.tagline') }}</span>
        </div>
    </div>
</footer>
</body>
</html>
