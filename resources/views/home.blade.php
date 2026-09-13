<x-layouts.public>
    @push('head')
        <x-seo :title="__('site.home.hero_title')" :json-ld="$jsonLd" />
    @endpush

    <section class="relative overflow-hidden rounded-3xl bg-linear-to-br from-indigo-600 via-violet-600 to-fuchsia-600 px-6 py-16 text-center text-white sm:px-12 sm:py-24">
        <div aria-hidden="true" class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full bg-fuchsia-400/40 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -bottom-32 -right-16 h-80 w-80 rounded-full bg-indigo-400/40 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -top-72 left-1/2 h-[36rem] w-[36rem] -translate-x-1/2 rounded-full border border-white/10"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -top-64 left-1/2 h-[32rem] w-[32rem] -translate-x-1/2 rounded-full border border-white/10"></div>

        <div class="relative">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3.5 py-1.5 text-xs font-semibold ring-1 ring-white/25 backdrop-blur">
                <x-heroicon-o-shield-check class="h-4 w-4 text-emerald-300" />
                {{ __('site.home.hero_badge') }}
            </span>
            <h1 class="mx-auto mt-6 max-w-3xl text-4xl font-extrabold tracking-tight sm:text-6xl">
                {{ __('site.home.hero_title') }}
            </h1>
            <p class="mx-auto mt-5 max-w-2xl text-base text-indigo-100 sm:text-lg">
                {{ __('site.home.hero_subtitle') }}
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('tools.index') }}"
                   class="rounded-xl bg-white px-6 py-3 text-sm font-semibold text-indigo-700 shadow-lg shadow-indigo-950/20 transition hover:bg-indigo-50">
                    {{ __('site.home.browse_all_tools') }}
                </a>
                <a href="#latest"
                   class="rounded-xl px-6 py-3 text-sm font-semibold text-white ring-1 ring-white/30 backdrop-blur transition hover:bg-white/10">
                    {{ __('site.home.latest_articles') }}
                </a>
            </div>
        </div>
    </section>

    <section id="latest" class="mt-14 scroll-mt-28">
        <x-section-heading :title="__('site.home.latest_articles')" />
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($articles as $article)
                <x-article-card :article="$article" />
            @empty
                <p class="text-sm text-zinc-500">{{ __('site.category.empty') }}</p>
            @endforelse
        </div>
    </section>

    @if ($tools->isNotEmpty())
        <section class="mt-14">
            <x-section-heading :title="__('site.home.top_tools')" :link-href="route('tools.index')" :link-text="__('site.home.view_all')" />
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($tools as $tool)
                    <x-tool-card :tool="$tool" />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.public>
