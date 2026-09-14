@props(['article'])

@php
    $locale = app()->getLocale();
    $excerpt = \Illuminate\Support\Str::limit(
        strval($article->getTranslation('excerpt', $locale) ?: strip_tags(strval($article->getTranslation('body_html', $locale)))),
        140,
    );
@endphp

<article class="group relative overflow-hidden rounded-2xl bg-white ring-1 ring-zinc-200/80 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-indigo-500/10 hover:ring-indigo-200 dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:ring-indigo-800">
    <a href="{{ route('articles.show', $article, false) }}" class="block focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
        <div class="relative overflow-hidden">
            @if ($article->cover_url !== null)
                <img src="{{ $article->cover_url }}" alt="{{ $article->getTranslation('title', $locale) }}"
                     loading="lazy" decoding="async"
                     class="aspect-[16/9] w-full object-cover transition duration-500 group-hover:scale-[1.04]">
            @else
                <div class="flex aspect-[16/9] w-full items-center justify-center bg-linear-to-br from-indigo-100 via-violet-100 to-fuchsia-100 dark:from-indigo-950 dark:via-violet-950 dark:to-fuchsia-950">
                    <x-heroicon-o-newspaper class="h-10 w-10 text-indigo-300 dark:text-indigo-700" />
                </div>
            @endif
            @if ($article->category !== null)
                <span class="absolute top-3 left-3 rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-indigo-700 shadow-sm backdrop-blur dark:bg-zinc-900/90 dark:text-indigo-400">
                    {{ $article->category->getTranslation('name', $locale) }}
                </span>
            @endif
        </div>
        <div class="p-5">
            <h3 class="text-lg font-semibold text-zinc-900 transition group-hover:text-indigo-600 dark:text-zinc-100 dark:group-hover:text-indigo-400">
                {{ $article->getTranslation('title', $locale) }}
            </h3>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $excerpt }}</p>
            <div class="mt-4 flex items-center gap-3 text-xs text-zinc-400 dark:text-zinc-500">
                <time datetime="{{ $article->published_at?->toIso8601String() }}">{{ $article->published_at?->format('M j, Y') }}</time>
                @if ($article->reading_time !== null)
                    <span>&middot; {{ __('site.article.min_read', ['min' => $article->reading_time]) }}</span>
                @endif
                <span aria-hidden="true" class="ml-auto -translate-x-1 text-indigo-600 opacity-0 transition duration-300 group-hover:translate-x-0 group-hover:opacity-100 dark:text-indigo-400">
                    <x-heroicon-o-arrow-right class="h-4 w-4" />
                </span>
            </div>
        </div>
    </a>
</article>
