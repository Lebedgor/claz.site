<x-layouts.public>
    @push('head')
        <x-seo
            :title="$name"
            :description="__('site.category.meta_description', ['name' => $name])"
            :json-ld="$jsonLd"
            :robots="$articles->currentPage() > 1 ? 'noindex,follow' : null"
        />
    @endpush

    <x-breadcrumbs :items="[
        ['label' => __('site.nav.home'), 'url' => route('home', [], false)],
        ['label' => $name],
    ]"/>

    <h1 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl dark:text-zinc-100">
        {{ __('site.category.articles_in', ['name' => $name]) }}
    </h1>
    <div aria-hidden="true" class="mt-3 h-1 w-20 rounded-full bg-linear-to-r from-indigo-500 to-fuchsia-500"></div>

    <p class="mt-4 max-w-2xl text-zinc-600 dark:text-zinc-400">
        {{ __('site.category.meta_description', ['name' => $name]) }}
    </p>

    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($articles as $article)
            <x-article-card :article="$article" />
        @empty
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('site.category.empty') }}</p>
        @endforelse
    </div>

    <div class="mt-8">{{ $articles->links() }}</div>
</x-layouts.public>
