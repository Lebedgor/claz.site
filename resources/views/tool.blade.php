@php
    $locale = app()->getLocale();
    $primaryLink = $tool->links->sortByDesc('is_affiliate')->sortBy('sort_order')->first();

    $formatValue = function (mixed $raw, string $kind): string {
        $decoded = is_string($raw) ? json_decode($raw) : $raw;

        if ($kind === 'bool') {
            return in_array($decoded, [true, 1, '1', 'true'], true) ? '✓' : '—';
        }

        return match (true) {
            $decoded === false, $decoded === null => '—',
            default => strval(is_scalar($decoded) ? $decoded : $raw),
        };
    };

    $jsonLd = [[
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $tool->getTranslation('name', $locale),
        'description' => strval($tool->getTranslation('description', $locale)),
        'url' => route('tools.show', $tool),
    ]];
@endphp

<x-layouts.public>
    @push('head')
        <x-seo
            :title="$tool->getTranslation('name', $locale)"
            :description="$tool->getTranslation('description', $locale) ?: null"
            :json-ld="$jsonLd"
        />
    @endpush

    <nav class="text-sm text-zinc-500">
        <a href="{{ route('home') }}" class="hover:text-indigo-600">{{ __('site.nav.home') }}</a>
        <span class="mx-1">/</span>
        <a href="{{ route('tools.index') }}" class="hover:text-indigo-600">{{ __('site.nav.tools') }}</a>
        <span class="mx-1">/</span>
        <span class="text-zinc-900">{{ $tool->getTranslation('name', $locale) }}</span>
    </nav>

    <div class="mt-4 flex flex-wrap items-center gap-4">
        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 text-xl font-bold text-white">
            {{ mb_strtoupper(mb_substr($tool->getTranslation('name', $locale), 0, 1)) }}
        </div>
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-zinc-900">{{ $tool->getTranslation('name', $locale) }}</h1>
            @if (filled($tool->vendor))
                <p class="text-sm text-zinc-500">{{ $tool->vendor }}</p>
            @endif
        </div>
        <span class="ml-auto flex items-center gap-2">
            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium uppercase tracking-wide text-indigo-700">
                {{ __('site.tools_index.type.'.$tool->type->value) }}
            </span>
            @if ($tool->rating_avg !== null)
                <span class="rounded-xl bg-amber-50 px-3 py-1.5 text-lg font-bold text-amber-700">
                    {{ number_format((float) $tool->rating_avg, 1) }}
                </span>
            @endif
        </span>
    </div>

    @if (filled($tool->getTranslation('description', $locale)))
        <p class="mt-6 max-w-3xl leading-relaxed text-zinc-700">
            {{ $tool->getTranslation('description', $locale) }}
        </p>
    @endif

    @if ($primaryLink !== null)
        <div class="mt-6">
            <a href="{{ url("/go/{$primaryLink->code}") }}"
               target="_blank"
               rel="sponsored nofollow noopener"
               class="inline-block rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                {{ $primaryLink->getTranslation('anchor', $locale) ?: __('site.tool.visit') }}
            </a>
        </div>
    @endif

    <section class="mt-10">
        <h2 class="text-xl font-semibold text-zinc-900">{{ __('site.tool.criteria') }}</h2>
        @if ($criteria->isEmpty())
            <p class="mt-3 text-sm text-zinc-500">{{ __('site.tool.no_criteria') }}</p>
        @else
            <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach ($criteria as $criterion)
                    <div class="flex items-center justify-between rounded-xl border border-zinc-200 bg-white px-4 py-3">
                        <dt class="text-sm font-medium text-zinc-700">
                            {{ $criterion->getTranslation('name', $locale) }}
                        </dt>
                        <dd class="text-sm font-semibold text-indigo-700">
                            {{ $formatValue($criterion->pivot->value, $criterion->kind->value) }}
                        </dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </section>

    <section class="mt-10">
        <h2 class="text-xl font-semibold text-zinc-900">{{ __('site.tool.mentioned_in') }}</h2>
        <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($relatedArticles as $relatedArticle)
                <x-article-card :article="$relatedArticle" />
            @empty
                <p class="text-sm text-zinc-500">{{ __('site.tool.no_articles') }}</p>
            @endforelse
        </div>
    </section>
</x-layouts.public>
