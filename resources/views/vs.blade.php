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

    <h1 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">{{ $title }}</h1>

    <p class="mt-4 max-w-3xl leading-relaxed text-zinc-600">{{ $intro }}</p>

    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('tools.show', $first, false) }}"
           class="inline-block rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:border-indigo-300 hover:text-indigo-700">
            {{ $first->getTranslation('name', $locale) }}
        </a>
        <a href="{{ route('tools.show', $second, false) }}"
           class="inline-block rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:border-indigo-300 hover:text-indigo-700">
            {{ $second->getTranslation('name', $locale) }}
        </a>
    </div>

    <x-comparison-table :comparison="$comparison" />

    <section class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-6">
        <h2 class="text-lg font-semibold text-indigo-900">{{ __('site.comparison.verdict') }}</h2>
        <p class="mt-2 text-zinc-700">{{ $conclusion }}</p>
    </section>
</x-layouts.public>
