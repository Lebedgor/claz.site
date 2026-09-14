<div>
    @push('head')
        <x-seo
            :title="__('site.articles_index.title')"
            :description="__('site.articles_index.meta_description')"
            :json-ld="$jsonLd"
            :robots="((int) $page) > 1 ? 'noindex,follow' : null"
        />
    @endpush

    <h1 class="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl dark:text-zinc-100">{{ __('site.articles_index.title') }}</h1>
    <div aria-hidden="true" class="mt-3 h-1 w-20 rounded-full bg-linear-to-r from-indigo-500 to-fuchsia-500"></div>

    <p class="mt-4 max-w-2xl text-zinc-600 dark:text-zinc-400">{{ __('site.articles_index.meta_description') }}</p>

    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($articles as $article)
            <x-article-card :article="$article" :wire:key="'article-'.$article->getKey()" />
        @empty
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('site.articles_index.empty') }}</p>
        @endforelse
    </div>

    @if ($hasMore)
        <div class="mt-8 flex justify-center">
            <button type="button" wire:click="loadMore" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-xl bg-linear-to-r from-indigo-600 to-violet-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 transition hover:shadow-indigo-500/40 hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                <span wire:loading.remove wire:target="loadMore" class="inline-flex items-center gap-2">
                    <x-heroicon-o-arrow-down class="h-4 w-4" />
                    {{ __('site.articles_index.show_more') }}
                </span>
                <span wire:loading wire:target="loadMore" class="inline-flex items-center gap-2">
                    <x-heroicon-o-arrow-path class="h-4 w-4 animate-spin" />
                    {{ __('site.articles_index.loading') }}
                </span>
            </button>
        </div>
    @endif

    <div class="mt-8">{{ $paginator->links() }}</div>
</div>
