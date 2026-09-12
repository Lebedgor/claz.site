@props(['article'])

@php
    $locale = app()->getLocale();
    $excerpt = \Illuminate\Support\Str::limit(
        strval($article->getTranslation('excerpt', $locale) ?: strip_tags(strval($article->getTranslation('body_html', $locale)))),
        140,
    );
@endphp

<article class="group overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition hover:shadow-md">
    <a href="{{ route('articles.show', $article) }}" class="block">
        @if ($article->cover !== null)
            <img src="{{ asset('storage/'.$article->cover) }}" alt="" class="aspect-[16/9] w-full object-cover">
        @else
            <div class="aspect-[16/9] w-full bg-gradient-to-br from-indigo-100 to-zinc-100"></div>
        @endif
        <div class="p-5">
            @if ($article->category !== null)
                <span class="text-xs font-medium uppercase tracking-wide text-indigo-600">
                    {{ $article->category->getTranslation('name', $locale) }}
                </span>
            @endif
            <h3 class="mt-1 text-lg font-semibold text-zinc-900 group-hover:text-indigo-700">
                {{ $article->getTranslation('title', $locale) }}
            </h3>
            <p class="mt-2 text-sm text-zinc-600">{{ $excerpt }}</p>
            <div class="mt-3 flex items-center gap-3 text-xs text-zinc-400">
                <span>{{ $article->published_at?->format('M j, Y') }}</span>
                @if ($article->reading_time !== null)
                    <span>&middot; {{ __('site.article.min_read', ['min' => $article->reading_time]) }}</span>
                @endif
            </div>
        </div>
    </a>
</article>
