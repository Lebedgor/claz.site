<x-layouts.public>
    @push('head')
        <x-seo :title="__('site.home.hero_title')" :json-ld="$jsonLd" />
    @endpush

    <section class="rounded-3xl bg-gradient-to-br from-indigo-600 to-violet-600 px-6 py-14 text-center text-white">
        <h1 class="text-3xl font-bold tracking-tight sm:text-5xl">{{ __('site.home.hero_title') }}</h1>
        <p class="mx-auto mt-4 max-w-2xl text-indigo-100">{{ __('site.home.hero_subtitle') }}</p>
        <a href="{{ route('tools.index') }}"
           class="mt-6 inline-block rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
            {{ __('site.home.browse_all_tools') }}
        </a>
    </section>

    <section class="mt-12">
        <h2 class="text-xl font-semibold text-zinc-900">{{ __('site.home.latest_articles') }}</h2>
        <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($articles as $article)
                <x-article-card :article="$article" />
            @empty
                <p class="text-sm text-zinc-500">{{ __('site.category.empty') }}</p>
            @endforelse
        </div>
    </section>

    @if ($tools->isNotEmpty())
        <section class="mt-12">
            <h2 class="text-xl font-semibold text-zinc-900">{{ __('site.home.top_tools') }}</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($tools as $tool)
                    <x-tool-card :tool="$tool" />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.public>
