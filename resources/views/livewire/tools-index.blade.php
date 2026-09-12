<div>
    @push('head')
        <x-seo :title="__('site.tools_index.title')" />
    @endpush

    <h1 class="text-3xl font-bold tracking-tight text-zinc-900">{{ __('site.tools_index.title') }}</h1>

    <div class="mt-5 flex flex-wrap items-center gap-2">
        <button wire:click="$set('type', '')"
                class="rounded-full px-3 py-1.5 text-xs font-medium {{ $type === '' ? 'bg-indigo-600 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
            {{ __('site.tools_index.all') }}
        </button>
        @foreach (\App\Enums\ToolType::cases() as $typeCase)
            <button wire:click="$set('type', '{{ $typeCase->value }}')"
                    class="rounded-full px-3 py-1.5 text-xs font-medium {{ $type === $typeCase->value ? 'bg-indigo-600 text-white' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200' }}">
                {{ __('site.tools_index.type.'.$typeCase->value) }}
            </button>
        @endforeach
        <input type="search"
               wire:model.live.debounce.300ms="search"
               placeholder="{{ __('site.tools_index.search_placeholder') }}"
               class="ml-auto w-56 rounded-xl border border-zinc-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($tools as $tool)
            <x-tool-card :tool="$tool" :wire:key="'tool-'.$tool->getKey()" />
        @empty
            <p class="text-sm text-zinc-500">{{ __('site.tools_index.empty') }}</p>
        @endforelse
    </div>

    <div class="mt-8">{{ $tools->links() }}</div>
</div>
