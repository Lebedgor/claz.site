@php
    $locale = app()->getLocale();
    $title = $first->getTranslation('name', $locale).' vs '.$second->getTranslation('name', $locale);
@endphp

<x-layouts.public>
    @push('head')
        <x-seo
            :title="$title"
            :description="$comparison->getTranslation('intro', $locale)"
            :json-ld="$jsonLd"
        />
    @endpush

    <x-breadcrumbs :items="[
        ['label' => __('site.nav.home'), 'url' => route('home', [], false)],
        ['label' => __('site.nav.tools'), 'url' => route('tools.index', [], false)],
        ['label' => $title],
    ]"/>

    <h1 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl dark:text-zinc-100">{{ $title }}</h1>

    <p class="mt-4 max-w-3xl leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $intro }}</p>

    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('tools.show', $first, false) }}"
           class="inline-block rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:border-indigo-300 hover:text-indigo-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:border-indigo-600 dark:hover:text-indigo-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
            {{ $first->getTranslation('name', $locale) }}
        </a>
        <a href="{{ route('tools.show', $second, false) }}"
           class="inline-block rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 transition hover:border-indigo-300 hover:text-indigo-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:border-indigo-600 dark:hover:text-indigo-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
            {{ $second->getTranslation('name', $locale) }}
        </a>
    </div>

    <x-comparison-table :comparison="$comparison" />

    <section class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-6 dark:border-indigo-900 dark:bg-indigo-950/60">
        <h2 class="text-lg font-semibold text-indigo-900 dark:text-indigo-300">{{ __('site.comparison.verdict') }}</h2>
        <p class="mt-2 text-zinc-700 dark:text-zinc-300">{{ $conclusion }}</p>
    </section>
</x-layouts.public>
