<x-layouts.public>
    @push('head')
        <x-seo
            :title="$category->getTranslation('name', app()->getLocale())"
            :description="$category->getTranslation('description', app()->getLocale()) ?: null"
        />
    @endpush

    <nav class="text-sm text-zinc-500">
        <a href="{{ route('home') }}" class="hover:text-indigo-600">{{ __('site.nav.home') }}</a>
        <span class="mx-1">/</span>
        <span class="text-zinc-900">{{ $category->getTranslation('name', app()->getLocale()) }}</span>
    </nav>

    <h1 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900">
        {{ __('site.category.articles_in', ['name' => $category->getTranslation('name', app()->getLocale())]) }}
    </h1>

    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($articles as $article)
            <x-article-card :article="$article" />
        @empty
            <p class="text-sm text-zinc-500">{{ __('site.category.empty') }}</p>
        @endforelse
    </div>

    <div class="mt-8">{{ $articles->links() }}</div>
</x-layouts.public>
