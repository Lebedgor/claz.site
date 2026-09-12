@php
    $locale = app()->getLocale();
    $coverUrl = $article->cover !== null ? asset('storage/'.$article->cover) : null;
@endphp

<x-layouts.public>
    @push('head')
        <x-seo
            :title="$article->getTranslation('meta_title', $locale) ?: $article->getTranslation('title', $locale)"
            :description="$article->getTranslation('meta_description', $locale) ?: $article->getTranslation('excerpt', $locale)"
            type="article"
            :image="$coverUrl"
            :json-ld="$jsonLd"
        />
    @endpush

    <nav class="text-sm text-zinc-500">
        <a href="{{ route('home') }}" class="hover:text-indigo-600">{{ __('site.nav.home') }}</a>
        @if ($article->category !== null)
            <span class="mx-1">/</span>
            <a href="{{ route('category.show', $article->category) }}" class="hover:text-indigo-600">
                {{ $article->category->getTranslation('name', $locale) }}
            </a>
        @endif
        <span class="mx-1">/</span>
        <span class="text-zinc-900">{{ \Illuminate\Support\Str::limit($article->getTranslation('title', $locale), 60) }}</span>
    </nav>

    <article class="mt-4">
        <h1 class="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
            {{ $article->getTranslation('title', $locale) }}
        </h1>

        <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-zinc-500">
            @if ($article->category !== null)
                <a href="{{ route('category.show', $article->category) }}"
                   class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                    {{ $article->category->getTranslation('name', $locale) }}
                </a>
            @endif
            <span>{{ $article->published_at?->format('M j, Y') }}</span>
            @if ($article->reading_time !== null)
                <span>&middot; {{ __('site.article.min_read', ['min' => $article->reading_time]) }}</span>
            @endif
        </div>

        @if ($coverUrl !== null)
            <img src="{{ $coverUrl }}" alt="" class="mt-6 aspect-[16/9] w-full rounded-2xl object-cover">
        @endif

        @if (filled($article->getTranslation('excerpt', $locale)))
            <p class="mt-6 border-l-4 border-indigo-200 pl-4 text-lg text-zinc-600">
                {{ $article->getTranslation('excerpt', $locale) }}
            </p>
        @endif

        <div class="mt-8 space-y-4 leading-relaxed text-zinc-800 [&_a]:font-medium [&_a]:text-indigo-600 [&_h2]:mt-8 [&_h2]:text-2xl [&_h2]:font-semibold [&_h2]:text-zinc-900 [&_h3]:mt-6 [&_h3]:text-xl [&_h3]:font-semibold [&_h3]:text-zinc-900 [&_img]:rounded-xl [&_li]:mt-1 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mt-3 [&_strong]:font-semibold [&_ul]:list-disc [&_ul]:pl-6">
            {!! $body !!}
        </div>

        @if ($article->tags->isNotEmpty())
            <div class="mt-8 flex flex-wrap gap-2">
                @foreach ($article->tags as $tag)
                    <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-600">
                        #{{ $tag->getTranslation('name', $locale) }}
                    </span>
                @endforeach
            </div>
        @endif
    </article>
</x-layouts.public>
