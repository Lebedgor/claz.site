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

        <div class="ex-article" style="line-height:1.55; color:#1f2937; font-size:15px;">
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

        <x-banner placement="sidebar" />

        <livewire:article-comments :article="$article" :wire:key="'comments-'.$article->getKey()" />
    </article>

    <div id="media-lightbox"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-zinc-950/95 p-4 backdrop-blur-sm opacity-0 transition-opacity duration-200"
         role="dialog" aria-modal="true" aria-label="Media viewer">
        <button id="lightbox-close" type="button" aria-label="Close"
                class="absolute top-4 right-4 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-zinc-300 ring-1 ring-white/15 backdrop-blur transition hover:bg-white/20 hover:text-white">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
        <button id="lightbox-prev" type="button" aria-label="Previous"
                class="absolute left-4 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-zinc-300 ring-1 ring-white/15 backdrop-blur transition hover:bg-white/20 hover:text-white">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
            </svg>
        </button>
        <button id="lightbox-next" type="button" aria-label="Next"
                class="absolute right-4 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-zinc-300 ring-1 ring-white/15 backdrop-blur transition hover:bg-white/20 hover:text-white">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
            </svg>
        </button>
        <figure class="max-w-6xl" id="lightbox-figure">
            <img id="lightbox-img" src="" alt=""
                 class="mx-auto max-h-[78vh] w-auto rounded-xl bg-zinc-900 shadow-2xl ring-1 ring-white/10">
            <figcaption class="mt-4 text-center">
                <span id="lightbox-caption" class="block text-sm text-zinc-200"></span>
                <span id="lightbox-counter" class="mt-2 hidden text-xs font-medium text-zinc-400"></span>
            </figcaption>
        </figure>
    </div>

    <script>
        (() => {
            const box = document.getElementById('media-lightbox');
            const figure = document.getElementById('lightbox-figure');
            const image = document.getElementById('lightbox-img');
            const caption = document.getElementById('lightbox-caption');
            const counter = document.getElementById('lightbox-counter');
            const prevButton = document.getElementById('lightbox-prev');
            const nextButton = document.getElementById('lightbox-next');
            const closeButton = document.getElementById('lightbox-close');
            const media = Array.from(document.querySelectorAll('article img'));

            let index = 0;
            let lastFocus = null;

            const render = () => {
                const current = media[index];
                image.src = current.src;
                image.alt = current.alt ?? '';
                caption.textContent = current.closest('figure')?.querySelector('figcaption')?.textContent ?? '';
                counter.textContent = media.length > 1 ? (index + 1) + ' / ' + media.length : '';

                const single = media.length <= 1;
                prevButton.classList.toggle('hidden', single);
                nextButton.classList.toggle('hidden', single);

                [index - 1, index + 1].forEach((offset) => {
                    const neighbor = media[(offset + media.length) % media.length];
                    if (neighbor && !neighbor.complete) {
                        new Image().src = neighbor.src;
                    }
                });
            };

            const open = (target) => {
                index = media.indexOf(target);
                if (index < 0) return;

                lastFocus = document.activeElement;
                render();
                box.classList.remove('hidden');
                box.classList.add('flex');
                requestAnimationFrame(() => box.classList.remove('opacity-0'));
                document.body.classList.add('overflow-hidden');
                closeButton.focus();
            };

            const close = () => {
                box.classList.add('opacity-0');
                setTimeout(() => {
                    box.classList.add('hidden');
                    box.classList.remove('flex');
                    image.src = '';
                }, 200);
                document.body.classList.remove('overflow-hidden');
                lastFocus?.focus();
            };

            const step = (delta) => {
                if (media.length === 0) return;
                index = (index + delta + media.length) % media.length;
                render();
            };

            media.forEach((item) => {
                item.classList.add('cursor-zoom-in');
                item.addEventListener('click', () => open(item));
            });

            prevButton.addEventListener('click', (event) => {
                event.stopPropagation();
                step(-1);
            });

            nextButton.addEventListener('click', (event) => {
                event.stopPropagation();
                step(1);
            });

            figure.addEventListener('click', (event) => event.stopPropagation());
            closeButton.addEventListener('click', close);
            box.addEventListener('click', close);

            document.addEventListener('keydown', (event) => {
                if (box.classList.contains('hidden')) return;
                if (event.key === 'Escape') close();
                if (event.key === 'ArrowLeft') step(-1);
                if (event.key === 'ArrowRight') step(1);
            });
        })();
    </script>
</x-layouts.public>
