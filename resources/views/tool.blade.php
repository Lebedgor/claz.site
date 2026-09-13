@php
    $locale = app()->getLocale();
    $toolName = $tool->getTranslation('name', $locale);
    $logoUrl = $tool->logo_url;
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

    $typeTints = [
        \App\Enums\ToolType::Plugin->value => 'bg-violet-50 text-violet-700 ring-violet-100',
        \App\Enums\ToolType::Service->value => 'bg-sky-50 text-sky-700 ring-sky-100',
        \App\Enums\ToolType::Ai->value => 'bg-fuchsia-50 text-fuchsia-700 ring-fuchsia-100',
    ];
@endphp

<x-layouts.public>
    @push('head')
        <x-seo
            :title="$toolName"
            :description="$tool->getTranslation('description', $locale) ?: null"
            :image="$logoUrl"
            :json-ld="$jsonLd"
        />
    @endpush

    <x-breadcrumbs :items="[
        ['label' => __('site.nav.home'), 'url' => route('home')],
        ['label' => __('site.nav.tools'), 'url' => route('tools.index')],
        ['label' => $toolName],
    ]"/>

    <div class="mt-6 grid gap-8 lg:grid-cols-[1fr_20rem] lg:items-start">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-bold tracking-tight text-zinc-900">{{ $toolName }}</h1>
                <span class="ml-auto flex items-center gap-2">
                    <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide ring-1 {{ $typeTints[$tool->type->value] }}">
                        {{ __('site.tools_index.type.'.$tool->type->value) }}
                    </span>
                    @if ($tool->rating_avg !== null)
                        <span class="rounded-xl bg-amber-50 px-3 py-1.5 text-lg font-bold text-amber-700">
                            {{ number_format((float) $tool->rating_avg, 1) }}
                        </span>
                    @endif
                </span>
            </div>
            @if (filled($tool->vendor))
                <p class="mt-1 text-sm text-zinc-500">{{ $tool->vendor }}</p>
            @endif

            @if (filled($tool->getTranslation('description', $locale)))
                <p class="mt-4 max-w-3xl leading-relaxed text-zinc-700">
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
        </div>

        @if ($logoUrl !== null)
            <div class="order-first lg:order-last">
                <img src="{{ $logoUrl }}" alt="{{ $toolName }}"
                     class="w-full aspect-square rounded-2xl object-cover ring-1 ring-zinc-200">
            </div>
        @endif
    </div>

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
