<div>
    @push('head')
        <x-seo
            :title="__('site.tools_index.title')"
            :description="__('site.tools_index.meta_description')"
            :json-ld="$jsonLd"
        />
    @endpush

    <h1 class="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl dark:text-zinc-100">{{ __('site.tools_index.title') }}</h1>
    <div aria-hidden="true" class="mt-3 h-1 w-20 rounded-full bg-linear-to-r from-indigo-500 to-fuchsia-500"></div>

    <p class="mt-4 max-w-2xl text-zinc-600 dark:text-zinc-400">{{ __('site.tools_index.meta_description') }}</p>

    <div class="mt-6 flex flex-wrap items-center gap-2">
        <button wire:click="$set('type', '')"
                class="rounded-full px-3.5 py-1.5 text-xs font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 {{ $type === '' ? 'bg-linear-to-r from-indigo-600 to-violet-600 text-white shadow-md shadow-indigo-500/30' : 'bg-white text-zinc-600 ring-1 ring-zinc-200 hover:text-indigo-700 hover:ring-indigo-300 dark:bg-zinc-800 dark:text-zinc-400 dark:ring-zinc-700 dark:hover:text-indigo-400 dark:hover:ring-indigo-600' }}">
            {{ __('site.tools_index.all') }}
        </button>
        @foreach (\App\Enums\ToolType::cases() as $typeCase)
            <button wire:click="$set('type', '{{ $typeCase->value }}')"
                    class="rounded-full px-3.5 py-1.5 text-xs font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 {{ $type === $typeCase->value ? 'bg-linear-to-r from-indigo-600 to-violet-600 text-white shadow-md shadow-indigo-500/30' : 'bg-white text-zinc-600 ring-1 ring-zinc-200 hover:text-indigo-700 hover:ring-indigo-300 dark:bg-zinc-800 dark:text-zinc-400 dark:ring-zinc-700 dark:hover:text-indigo-400 dark:hover:ring-indigo-600' }}">
                {{ __('site.tools_index.type.'.$typeCase->value) }}
            </button>
        @endforeach
        <div class="relative ml-auto">
            <x-heroicon-o-magnifying-glass class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-zinc-400 dark:text-zinc-500" />
            <input type="search"
                   wire:model.live.debounce.300ms="search"
                   placeholder="{{ __('site.tools_index.search_placeholder') }}"
                   class="w-full rounded-xl bg-white py-2 pr-3 pl-9 text-sm ring-1 ring-zinc-200 transition focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:bg-zinc-900 dark:text-zinc-100 dark:ring-zinc-700 sm:w-64">
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($tools as $tool)
            <x-tool-card :tool="$tool" :wire:key="'tool-'.$tool->getKey()" />
        @empty
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('site.tools_index.empty') }}</p>
        @endforelse
    </div>

    <div class="mt-8">{{ $tools->links() }}</div>
</div>
